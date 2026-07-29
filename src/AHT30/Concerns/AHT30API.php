<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\Enums\AHT30OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns\AHTx0InternalAPI;

trait AHT30API
{
    use AHTx0InternalAPI;

    /**
     * Always sends initialize. `$requireStatus` controls the calibrated-bit check.
     *
     * @throws AHTx0Exception
     */
    public function calibrate(bool $requireStatus = true): bool
    {
        $this->send(AHT30OpCode::CMD_INITIALIZE->value, $this->_calibrate());

        return $this->finishCalibration($requireStatus);
    }

    /**
     * AHT30's measurement response is one indivisible transaction:
     * status + five data bytes + CRC. Reading status separately consumes the sample.
     */
    protected function shouldPollAfterMeasurementTrigger(): bool
    {
        return false;
    }

    protected function measurementResponseLength(): int
    {
        return 7;
    }
}
