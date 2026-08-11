<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Sketches;

use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0CatalogIc;
use Waveforms\Contracts\Sensors\Enums\HumidityUnit;
use Waveforms\Contracts\Sensors\Enums\TemperatureUnit;
use Fabricate\Contracts\Sketches\Attributes\Sketch as SketchAttribute;
use Fabricate\Contracts\Sketches\SketchLoopResult;
use Fabricate\Sketches\Sketch;
use GeneralPurposeIO\Circuits\Types\SensorIC;
use GeneralPurposeIO\Contracts\Circuits\IntegratedCircuit;
use GeneralPurposeIO\Core\MagicAliases\Circuit;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

#[SketchAttribute('ahtx0-smoke')]
class AHTx0Smoke extends Sketch
{
    protected string $description = 'Smoke-test a provisioned AHTx0 profile (Ctrl-C to end)';

    protected ?IntegratedCircuit $sensor = null;

    protected ?string $profileName = null;

    protected bool $stopRequested = false;

    protected bool $announced = false;

    protected int $lastSampleNs = 0;

    public function configureCommand(Command $command): void
    {
        $command->addOption(
            'profile',
            null,
            InputOption::VALUE_OPTIONAL,
            'circuits.php profile name (ic must be aht10/aht20/aht30)',
        );
    }

    public function boot(): void
    {
        if (extension_loaded('pcntl')) {
            pcntl_async_signals(true);
            $stop = function (): void {
                $this->stopRequested = true;
            };
            pcntl_signal(SIGINT, $stop);
            pcntl_signal(SIGTERM, $stop);
        }

        $profiles = $this->ahtx0Profiles();
        if ($profiles === []) {
            $this->error('No AHTx0 profiles in config/circuits.php. Run: php workshop ahtx0:make-profile');

            return;
        }

        $requested = $this->option('profile');
        if (is_string($requested) && $requested !== '') {
            if (! isset($profiles[$requested])) {
                $this->error("Profile [{$requested}] is missing or not an AHTx0 ic.");

                return;
            }
            $this->profileName = $requested;
        } elseif (count($profiles) === 1) {
            $this->profileName = array_key_first($profiles);
        } else {
            $this->profileName = $this->choice('Which AHTx0 profile?', array_keys($profiles));
        }

        try {
            $this->sensor = Circuit::profile($this->profileName);
        } catch (Throwable $e) {
            $this->error($e->getMessage());
            $this->sensor = null;
        }
    }

    public function loop(): SketchLoopResult
    {
        if ($this->stopRequested) {
            $this->info('AHTx0 smoke stopped.');

            return SketchLoopResult::STOP;
        }

        if (is_null($this->sensor) || is_null($this->profileName)) {
            return SketchLoopResult::STOP;
        }

        if (! $this->announced) {
            $ic = (string) (config("circuits.{$this->profileName}.ic") ?? 'ahtx0');
            $this->info("AHTx0 smoke via Circuit::profile('{$this->profileName}') [{$ic}]");
            $this->line('  Sampling °C / %RH — Ctrl-C to end.');
            $this->announced = true;
        }

        $now = hrtime(true);
        if ($this->lastSampleNs !== 0 && ($now - $this->lastSampleNs) < 1_000_000_000) {
            usleep(20_000);

            return SketchLoopResult::CONTINUE;
        }

        try {
            $temp = method_exists($this->sensor, 'temperature')
                ? $this->sensor->temperature(TemperatureUnit::CELSIUS)
                : null;
            $rh = method_exists($this->sensor, 'humidity')
                ? $this->sensor->humidity(HumidityUnit::PERCENT)
                : null;
            $this->line(sprintf(
                '  T=%.2f°C  RH=%.1f%%',
                $temp ?? NAN,
                $rh ?? NAN,
            ));
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return SketchLoopResult::STOP;
        }

        $this->lastSampleNs = $now;

        return SketchLoopResult::CONTINUE;
    }

    public function shutdown(): void
    {
        if ($this->sensor instanceof SensorIC || $this->sensor instanceof IntegratedCircuit) {
            try {
                $this->sensor->close();
            } catch (Throwable) {
                //
            }
        }
        $this->sensor = null;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function ahtx0Profiles(): array
    {
        $all = config('circuits', []);
        if (! is_array($all)) {
            return [];
        }

        $matched = [];
        foreach ($all as $name => $recipe) {
            if (! is_string($name) || ! is_array($recipe)) {
                continue;
            }
            $ic = $recipe['ic'] ?? null;
            if (is_string($ic) && ! is_null(AHTx0CatalogIc::tryFrom($ic))) {
                $matched[$name] = $recipe;
            }
        }

        return $matched;
    }
}
