<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Concerns\AHT10API;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0CarrierTransport;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;
use Exception;
use Fabricate\Contracts\Circuits\Attributes\IntegratedCircuit;
use Fabricate\Contracts\Circuits\IntegratedCircuit as CircuitContract;
use Fabricate\Contracts\NutsAndBolts\BootSequence;
use Fabricate\Contracts\Sensors\Enums\HumidityUnit;
use Fabricate\Contracts\Sensors\Enums\TemperatureUnit;
use Fabricate\Contracts\Sensors\Interfaces\Hygrometer;
use Fabricate\Contracts\Sensors\Interfaces\Thermometer;
use GeneralPurposeIO\I2C\I2C;
use GeneralPurposeIO\I2C\I2CSlave;

/**
 * @property float $relative_humidity
 * @property float $temperature
 * @property int $status
 */
#[IntegratedCircuit('I2C')]
class AHT10 implements CircuitContract, BootSequence, Hygrometer, Thermometer
{
    use AHT10API;

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
            'relative_humidity' => $this->measureHumidity(HumidityUnit::PERCENT),
            'temperature' => $this->measureTemp(TemperatureUnit::CELSIUS),
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
