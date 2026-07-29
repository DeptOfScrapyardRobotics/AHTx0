<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\Enums\AHT10OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0InternalAPI;

trait AHT10API
{
    use AHTx0InternalAPI;

    /**
     * Always sends initialize. `$requireStatus` controls the calibrated-bit check.
     *
     * @throws AHTx0Exception
     */
    public function calibrate(bool $requireStatus = true): bool
    {
        if (! $this->send(AHT10OpCode::CMD_INITIALIZE->value, $this->_calibrate())) {
            // Without a successful init write, measurements usually NACK 0xAC.
            return false;
        }

        return $this->finishCalibration($requireStatus);
    }
}
