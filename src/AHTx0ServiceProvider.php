<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\AHT10;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\AHT30;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Console\AHTx0MakeProfileCommand;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0CatalogIc;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0ConsoleCommand;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Sketches\AHTx0Smoke;
use Fabricate\Contracts\Sketches\SketchRegistry;
use Fabricate\NutsAndBolts\ServiceProvider;
use GeneralPurposeIO\Core\MagicAliases\Circuit;

class AHTx0ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->container->singleton(AHTx0MakeProfileCommand::class);
        $this->commands([
            AHTx0MakeProfileCommand::class,
        ]);
    }

    public function boot(): void
    {
        Circuit::addCircuit(AHTx0CatalogIc::AHT10->value, AHT10::class);
        Circuit::addCircuit(AHTx0CatalogIc::AHT20->value, AHT20::class);
        Circuit::addCircuit(AHTx0CatalogIc::AHT30->value, AHT30::class);

        $maker = AHTx0ConsoleCommand::MAKE_PROFILE->value;
        foreach (AHTx0CatalogIc::cases() as $ic) {
            Circuit::registerProfileCommand($ic->value, $maker);
        }

        $this->registerSketch();
    }

    protected function registerSketch(): void
    {
        if (! $this->container->bound(SketchRegistry::class)) {
            return;
        }

        /** @var SketchRegistry $registry */
        $registry = $this->container->make(SketchRegistry::class);

        if (! $registry->has('ahtx0-smoke')) {
            $registry->registerConvention('ahtx0-smoke', AHTx0Smoke::class);
        }
    }
}
