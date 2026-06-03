<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Enums\AHT10OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0API;

trait AHT10API
{
    use AHTx0API;

    public function calibrate(): bool
    {
        return $this->send(AHT10OpCode::CMD_INITIALIZE->value, $this->_calibrate());
    }

    public function getRelativeHumidity(): float {}

    public function getStatus() {}

    public function getTemp(): float {}
}
