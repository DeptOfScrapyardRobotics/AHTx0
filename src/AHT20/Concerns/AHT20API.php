<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Enums\AHT20OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0InternalAPI;

trait AHT20API
{
    use AHTx0InternalAPI;

    /**
     * Always sends initialize. `$requireStatus` controls the calibrated-bit check.
     *
     * @throws AHTx0Exception
     */
    public function calibrate(bool $requireStatus = true): bool
    {
        // Newer AHT20s may NACK the initialize write; status / measure path is authoritative.
        $this->send(AHT20OpCode::CMD_INITIALIZE->value, $this->_calibrate());

        return $this->finishCalibration($requireStatus);
    }
}
