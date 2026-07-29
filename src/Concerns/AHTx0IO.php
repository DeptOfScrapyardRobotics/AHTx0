<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;

trait AHTx0IO
{
    /**
     * Pure I2C read (command sensors — no register address prefix).
     *
     * @return array<int, int>
     *
     * @throws AHTx0Exception
     */
    protected function i2cRead(int $length): array
    {
        if (! is_null($this->i2c)) {
            $bytes = $this->i2c->read($length);

            if ($bytes === false) {
                throw AHTx0Exception::i2cReadFailed($length);
            }

            return array_values($bytes);
        }

        throw AHTx0Exception::transportMissingProtocol();
    }

    /**
     * Pure I2C write of command payload bytes.
     *
     * @param  array<int, int>  $data
     *
     * @throws AHTx0Exception
     */
    protected function i2cWrite(array $data): int
    {
        if (! is_null($this->i2c)) {
            return $this->i2c->write($data);
        }

        throw AHTx0Exception::transportMissingProtocol();
    }
}
