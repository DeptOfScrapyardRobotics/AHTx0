<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Exceptions;

use Exception;

class AHTException extends Exception
{
    public static function invalidProperty(string $name): static
    {
        return new static("Invalid property $name");
    }

    public static function measurementTimeout(): static
    {
        return new static('AHTx0 measurement timeout — busy flag did not clear within the expected window');
    }

    public static function invalidResponseLength(int $length): static
    {
        return new static("AHTx0 invalid response length — expected 6 bytes, got {$length}");
    }
}
