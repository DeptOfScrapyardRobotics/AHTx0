<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Enums\AHT30OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0API;

trait AHT30API
{
    use AHTx0API;

    public function calibrate(): bool
    {
        return $this->send(AHT30OpCode::CMD_INITIALIZE->value, $this->_calibrate());
    }

    public function getRelativeHumidity(): float {}

    public function getStatus() {}

    public function getTemp(): float {}
}
