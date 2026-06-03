<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Enums\AHT20OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0API;

trait AHT20API
{
    use AHTx0API;

    public function calibrate(): bool
    {
        return $this->send(AHT20OpCode::CMD_INITIALIZE->value, $this->_calibrate());
    }
}
