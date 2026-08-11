<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Concerns\AHT30API;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0CarrierTransport;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;
use Waveforms\Contracts\Environment\MeasuresRelativeHumidity;
use Waveforms\Contracts\Environment\MeasuresTemperature;
use Waveforms\Contracts\Sensors\Enums\HumidityUnit;
use Waveforms\Contracts\Sensors\Enums\TemperatureUnit;
use Exception;
use GeneralPurposeIO\Circuits\Types\SensorIC;
use GeneralPurposeIO\Contracts\Circuits\Attributes\IntegratedCircuit;
use GeneralPurposeIO\Contracts\Circuits\Attributes\Pinout;
use GeneralPurposeIO\Contracts\Circuits\BootSequence;
use GeneralPurposeIO\I2C\I2C;
use GeneralPurposeIO\I2C\I2CSlave;

/**
 * @property float $relative_humidity
 * @property float $temperature
 * @property int $status
 */
#[IntegratedCircuit('I2C')]
#[Pinout(['I2C' => ['driver', 'device', 'slave']])]
class AHT30 extends SensorIC implements BootSequence, MeasuresTemperature, MeasuresRelativeHumidity
{
    use AHT30API;

    /**
     * @throws Exception
     */
    public function __construct(
        protected readonly AHTx0CarrierTransport $transport,
        bool $boot_now = false,
        bool $calibrate = true,
    ) {
        $this->calibrate_on_boot = $calibrate;

        if ($boot_now) {
            $this->boot();
        }
    }

    /**
     * @throws AHTx0Exception
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'relative_humidity' => $this->humidity(HumidityUnit::PERCENT),
            'temperature' => $this->temperature(TemperatureUnit::CELSIUS),
            'status' => $this->getStatus(),
            default => throw AHTx0Exception::invalidProperty($name, static::class),
        };
    }

    public function close(): void
    {
        $this->transport->close();
    }

    /**
     * @throws AHTx0Exception
     */
    public static function i2c(
        string|int $device,
        ?string $adapter = null,
        int $slave = AHTx0I2CAddress::DEFAULT->value,
        bool $boot_now = true,
        bool $calibrate = true,
    ): static {
        $i2c = I2C::adapter($adapter)
            ->device($device)
            ->bus()
            ->slave($slave);

        return static::fromI2CBus($i2c, $boot_now, $calibrate);
    }

    /**
     * @throws AHTx0Exception
     * @throws Exception
     */
    public static function fromI2CBus(
        I2CSlave $i2c,
        bool $boot_now = true,
        bool $calibrate = true,
    ): static {
        $transport = new AHTx0CarrierTransport(i2c: $i2c);

        return new static($transport, $boot_now, $calibrate);
    }
}
