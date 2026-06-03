<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30;

use BareMetal\IntegratedCircuit;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Concerns\AHT30API;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Exceptions\AHT30Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Factory\AHT30Factory;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Exceptions\AHTException;
use Exception;
use RealityInterface\Sensors\Attributes\MeasuresRelativeHumidity;
use RealityInterface\Sensors\Attributes\MeasuresTemperature;
use RealityInterface\Sensors\Contracts\Applied\Environmental\RHSensor;
use RealityInterface\Sensors\Contracts\Applied\Environmental\TemperatureSensor;
use RealityInterface\Sensors\Enums\SensorType;
use Waveforms\Carriers\I2C\I2C;
use Waveforms\Carriers\I2C\I2CDevice;

/**
 * @property float $relative_humidity
 * @property float $temperature
 * @property int $status
 */
#[MeasuresTemperature(SensorType::TEMPERATURE)]
#[MeasuresRelativeHumidity(SensorType::RELATIVE_HUMIDITY)]
class AHT30 extends IntegratedCircuit implements RHSensor, TemperatureSensor
{
    use AHT30API;

    protected bool $booted = false;

    /**
     * @throws AHTException
     */
    public function __construct(
        protected readonly I2CDevice $carrier,
    ) {
        $this->boot();
    }

    public function getTemperature(): ?float
    {
        return $this->temperature;
    }

    public function getHumidity(): ?float
    {
        return $this->relative_humidity;
    }

    /**
     * @throws AHT30Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'relative_humidity' => $this->getRelativeHumidity(),
            'temperature' => $this->getTemperature(),
            'status' => $this->getStatus(),
            default => throw AHT30Exception::invalidProperty($name)
        };
    }

    /**
     * @throws AHT30Exception
     */
    protected function boot(): void
    {
        if (! $this->booted) {
            $this->reset();
            if (! $this->calibrate()) {
                throw new AHT30Exception('AHT30 needs to be calibrated');
            }

            $this->booted = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function connection(string $driver): AHT30Factory
    {
        return new AHT30Factory(
            I2C::connection($driver)
        );
    }
}
