<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Factory;

use BareMetal\CircuitFactory;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\AHT30;
use Exception;
use Waveforms\Carriers\I2C\Factory\I2CConnectionBuilder;

class AHT30Factory extends CircuitFactory
{
    public ?I2CConnectionBuilder $connection = null;

    public function __construct(
        public I2CConnectionBuilder $i2c_connection,

    ) {}

    public function i2c(string|int $chip_device, int $slave_address): static
    {
        $this->connection = $this->i2c_connection->firstly($chip_device)
            ->slaveAddress($slave_address);

        return $this;
    }

    /**
     * @throws Exception
     */
    public function create(): AHT30
    {
        $carrier = $this->connection?->boot();
        if (is_null($carrier)) {
            throw new Exception('A connection was not registered.');
        }

        return new AHT30(
            $carrier
        );
    }
}
