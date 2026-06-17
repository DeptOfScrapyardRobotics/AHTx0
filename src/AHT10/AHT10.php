<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Concerns\AHT10API;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Exceptions\AHT10Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Factory\AHT10Factory;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Exceptions\AHTException;
use Exception;
use RealityInterface\Sensors\Attributes\MeasuresRelativeHumidity;
use RealityInterface\Sensors\Attributes\MeasuresTemperature;
use RealityInterface\Sensors\Contracts\Applied\Environmental\RHSensor;
use RealityInterface\Sensors\Contracts\Applied\Environmental\TemperatureSensor;
use RealityInterface\Sensors\Enums\SensorType;
use RealityInterface\Sensors\SensorChip;
use Waveforms\Carriers\I2C\I2C;
use Waveforms\Carriers\I2C\I2CDevice;

/**
 * @property float $relative_humidity
 * @property float $temperature
 * @property int $status
 */
#[MeasuresTemperature(SensorType::TEMPERATURE)]
#[MeasuresRelativeHumidity(SensorType::RELATIVE_HUMIDITY)]
class AHT10 extends SensorChip implements RHSensor, TemperatureSensor
{
    use AHT10API;

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
     * @throws AHT10Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'relative_humidity' => $this->getRelativeHumidity(),
            'temperature' => $this->getTemperature(),
            'status' => $this->getStatus(),
            default => throw AHT10Exception::invalidProperty($name)
        };
    }

    /**
     * @throws AHT10Exception
     */
    protected function boot(): void
    {
        if (! $this->booted) {
            $this->reset();
            if (! $this->calibrate()) {
                throw new AHT10Exception('AHT10 needs to be calibrated');
            }

            $this->booted = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function connection(string $driver): AHT10Factory
    {
        return new AHT10Factory(
            I2C::connection($driver)
        );
    }
}
