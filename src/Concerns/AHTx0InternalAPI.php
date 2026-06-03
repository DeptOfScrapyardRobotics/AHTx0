<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Exceptions\AHTException;

trait AHTx0InternalAPI
{
    protected int $status_busy_mask = 0x80;

    protected int $status_calibrated_mask = 0x08;

    protected function send(int $cmd, array $body = []): bool
    {
        $payload = [$cmd & 0xFF, ...$body];

        return $this->carrier->write($payload);
    }

    protected function readStatus(): int
    {
        $bytes = $this->carrier->read(1);

        return $bytes[0] ?? 0;
    }

    protected function _calibrate(): array
    {
        $busy = '0';
        $cmd_mode = '0';
        $continuous_readings = '0';
        $bit4 = '0';
        $run_calibration = '1';
        $bit2 = $bit1 = $bit0 = '0';

        return [
            bindec("{$busy}{$cmd_mode}{$continuous_readings}{$bit4}{$run_calibration}{$bit2}{$bit1}{$bit0}"),
            0x00,
        ];
    }

    /**
     * @throws AHTException
     */
    protected function triggerMeasurement(): void
    {
        $measurement_setting = 0x33;
        $reserved = 0x00;
        if ($this->send(AHTx0OpCode::CMD_TRIGGER->value, [$measurement_setting, $reserved])) {
            for ($i = 0; $i < 10; $i++) {
                usleep(10_000);
                $status = $this->readStatus();
                if (! $this->isBusy($status)) {
                    return;
                }
            }

            throw AHTException::measurementTimeout();
        }
    }

    /**
     * @throws AHTException
     */
    protected function pullMeasurement(): array
    {
        $data = $this->carrier->read(6);

        if (count($data) !== 6) {
            throw AHTException::invalidResponseLength(count($data));
        }

        $humidity = (($data[1] & 0xFF) << 12)
            | (($data[2] & 0xFF) << 4)
            | (($data[3] & 0xFF) >> 4);

        $temperature = (($data[3] & 0x0F) << 16)
            | (($data[4] & 0xFF) << 8)
            | ($data[5] & 0xFF);

        return [
            $this->compensateHumidity($humidity),
            $this->compensateTemperature($temperature),
        ];
    }

    protected function compensateHumidity(int $rawHumidity): float
    {
        return ($rawHumidity / 1_048_576.0) * 100.0;
    }

    protected function compensateTemperature(int $rawTemperature): float
    {
        return ($rawTemperature / 1_048_576.0) * 200.0 - 50.0;
    }

    protected function isCalibrated(int $status): bool
    {
        return ($status & $this->status_calibrated_mask) !== 0;
    }

    protected function isBusy(int $status): bool
    {
        return ($status & $this->status_busy_mask) !== 0;
    }
}
