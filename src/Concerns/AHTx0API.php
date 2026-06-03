<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Exceptions\AHTException;

trait AHTx0API
{
    use AHTx0InternalAPI;

    public function reset(): void
    {
        $this->send(AHTx0OpCode::CMD_SOFT_RESET->value);
        usleep(20000);
    }

    public function getRelativeHumidity(): float
    {
        try {
            $this->triggerMeasurement();
            $measurement = $this->pullMeasurement();
            $results = $measurement[0];
        } catch (AHTException) {
            $results = null;
        }

        return $results;
    }

    public function getStatus(): int {}

    public function getTemp(): float
    {
        try {
            $this->triggerMeasurement();
            $measurement = $this->pullMeasurement();
            $results = $measurement[1];
        } catch (AHTException) {
            $results = null;
        }

        return $results;
    }
}
