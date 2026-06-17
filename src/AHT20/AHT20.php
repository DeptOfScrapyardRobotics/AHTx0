<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Concerns\AHT20API;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Exceptions\AHT20Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Factory\AHT20Factory;
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
class AHT20 extends SensorChip implements RHSensor, TemperatureSensor
{
    use AHT20API;

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
     * @throws AHT20Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'relative_humidity' => $this->getRelativeHumidity(),
            'temperature' => $this->getTemp(),
            'status' => $this->getStatus(),
            default => throw AHT20Exception::invalidProperty($name)
        };
    }

    /**
     * @throws AHT20Exception
     */
    protected function boot(): void
    {
        if (! $this->booted) {
            $this->reset();
            if (! $this->calibrate()) {
                throw new AHT20Exception('AHT20 needs to be calibrated');
            }

            $this->booted = true;
        }
    }

    /**
     * @throws Exception
     */
    public static function connection(string $driver): AHT20Factory
    {
        return new AHT20Factory(
            I2C::connection($driver)
        );
    }
}
