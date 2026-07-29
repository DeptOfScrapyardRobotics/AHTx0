<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0;

use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0IO;
use GeneralPurposeIO\I2C\I2CSlave;

class AHTx0CarrierTransport
{
    use AHTx0IO;

    public readonly string $active_transport;

    /**
     * @throws AHTx0Exception
     */
    public function __construct(
        protected ?I2CSlave $i2c = null,
    ) {
        $this->active_transport = $this->detectTransport();
    }

    /**
     * @param  array<int, int>  $data
     *
     * @throws AHTx0Exception
     */
    public function write(array $data): int
    {
        $method = "{$this->active_transport}Write";

        return $this->{$method}($data);
    }

    /**
     * @return array<int, int>
     *
     * @throws AHTx0Exception
     */
    public function read(int $length): array
    {
        $method = "{$this->active_transport}Read";

        return $this->{$method}($length);
    }

    /**
     * @throws AHTx0Exception
     */
    protected function detectTransport(): string
    {
        if (! is_null($this->i2c)) {
            return 'i2c';
        }

        throw AHTx0Exception::transportMissingProtocol();
    }

    public function close(): void
    {
        $this->i2c?->close();
    }
}
