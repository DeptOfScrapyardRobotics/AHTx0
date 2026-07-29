<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0;

use Fabricate\Contracts\Circuits\CircuitException;

class AHTx0Exception extends CircuitException
{
    public static function measurementTimeout(): static
    {
        return new static('AHTx0 measurement timeout — busy flag did not clear within the expected window');
    }

    public static function calibrationTimeout(): static
    {
        return new static('AHTx0 calibration timeout — busy flag did not clear within the expected window');
    }

    public static function invalidResponseLength(int $length, int $expected = 6): static
    {
        return new static("AHTx0 invalid response length — expected {$expected} bytes, got {$length}");
    }

    public static function emptyMeasurementFrame(): static
    {
        return new static('AHTx0 returned an empty measurement frame.');
    }

    public static function i2cReadFailed(int $length): static
    {
        return new static("AHTx0 I2C read of {$length} byte(s) failed.");
    }

    public static function commandWriteFailed(int $command): static
    {
        $hex = strtoupper(str_pad(dechex($command), 2, '0', STR_PAD_LEFT));

        return new static("AHTx0 failed to write command 0x{$hex}.");
    }

    public static function transportMissingProtocol(): static
    {
        return new static('AHTx0 devices require an I2C capable connection.');
    }
}
