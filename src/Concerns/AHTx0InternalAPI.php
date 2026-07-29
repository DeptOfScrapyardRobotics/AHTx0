<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Concerns;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0Exception;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0OpCode;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0StatusFlag;
use Fabricate\Contracts\NutsAndBolts\BootScaffolding;
use Fabricate\Contracts\Sensors\Enums\HumidityUnit;
use Fabricate\Contracts\Sensors\Enums\TemperatureUnit;
use Fabricate\NutsAndBolts\Concerns\Splices16Bits;

trait AHTx0InternalAPI
{
    use BootScaffolding, Splices16Bits;

    /**
     * When true, {@see _boot()} requires the calibrated status bit after init.
     * When false, boot still sends the initialize command (needed before 0xAC
     * measurements) but does not require the calibrated status bit — common on clones.
     */
    protected bool $calibrate_on_boot = true;

    /**
     * @throws AHTx0Exception
     */
    public function measureTemp(TemperatureUnit $unit): float
    {
        return TemperatureUnit::CELSIUS->convert($this->readCelsius(), $unit);
    }

    /**
     * @throws AHTx0Exception
     */
    public function measureHumidity(HumidityUnit $unit = HumidityUnit::PERCENT): float
    {
        return HumidityUnit::PERCENT->convert($this->readRelativeHumidityPercent(), $unit);
    }

    /**
     * @throws AHTx0Exception
     */
    public function getStatus(): int
    {
        return $this->readStatus();
    }

    /**
     * Fresh relative humidity sample in percent.
     *
     * @throws AHTx0Exception
     */
    protected function readRelativeHumidityPercent(): float
    {
        return $this->sampleMeasurement()[0];
    }

    /**
     * Fresh temperature sample in Celsius.
     *
     * @throws AHTx0Exception
     */
    protected function readCelsius(): float
    {
        return $this->sampleMeasurement()[1];
    }

    /**
     * Trigger + pull, retrying when the frame is status-only zeros
     * (0x18 00 00 00 00 00 → -50°C / 0% RH) — seen on AHT30 after AHT20-style 0xBE init.
     *
     * @return array{0: float, 1: float}
     *
     * @throws AHTx0Exception
     */
    protected function sampleMeasurement(): array
    {
        $last = null;

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $this->triggerMeasurement();
            $last = $this->pullMeasurement();

            if (! $this->isEmptyMeasurementFrame($last['raw'])) {
                return [$last['humidity'], $last['temperature']];
            }
        }

        throw AHTx0Exception::emptyMeasurementFrame();
    }

    /**
     * @param  array<int, int>  $raw
     */
    protected function isEmptyMeasurementFrame(array $raw): bool
    {
        if (count($raw) < 6) {
            return true;
        }

        return ($raw[1] | $raw[2] | $raw[3] | $raw[4] | $raw[5]) === 0;
    }

    /**
     * @param  array<int, int>  $body
     *
     * @throws AHTx0Exception
     */
    protected function send(int $cmd, array $body = []): bool
    {
        $payload = [$this->getLowByte($cmd), ...$body];
        $written = $this->transport->write($payload);
        $ok = $written >= count($payload);

        // Drivers report bytes written (strlen), which matches payload length on success.
        // A negative return means the address/command was NACKed.
        return $ok;
    }

    /**
     * @throws AHTx0Exception
     */
    protected function readStatus(): int
    {
        $bytes = $this->transport->read(1);

        return $bytes[0] ?? 0xFF;
    }

    /**
     * Soft-reset the sensor.
     *
     * The 0xBA write is best-effort: some AHT10/20 clones NACK the command byte
     * while still resetting. Calibration status after the settle wait is authoritative.
     *
     * @throws AHTx0Exception
     */
    public function reset(): void
    {
        $this->send(AHTx0OpCode::CMD_SOFT_RESET->value);
        usleep(20_000);
        $this->waitUntilNotBusy(
            maxAttempts: 100,
            timeoutException: AHTx0Exception::calibrationTimeout(),
        );
    }

    /**
     * Calibration payload bytes following the initialize command: 0x08 0x00.
     *
     * @return array{0: int, 1: int}
     */
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
     * After an initialize/calibrate write: wait for busy clear, optionally require calibrated bit.
     *
     * @throws AHTx0Exception
     */
    protected function finishCalibration(bool $requireStatus = true): bool
    {
        try {
            $status = $this->waitUntilNotBusy(
                maxAttempts: 300,
                timeoutException: AHTx0Exception::calibrationTimeout(),
            );
        } catch (AHTx0Exception $e) {
            if ($requireStatus) {
                throw $e;
            }

            return true;
        }

        return ! $requireStatus || $this->isCalibrated($status);
    }

    /**
     * @throws AHTx0Exception
     */
    protected function triggerMeasurement(): void
    {
        $measurement_setting = 0x33;
        $reserved = 0x00;

        // Status/busy check before 0xAC. After USB-I2C idle, the status read often
        // fails and the next 0xAC NACKs — re-init restores the bus (see debug session).
        $needsReinit = false;
        try {
            $this->waitUntilNotBusy(maxAttempts: 10, timeoutException: AHTx0Exception::measurementTimeout());
        } catch (AHTx0Exception) {
            $needsReinit = true;
        }

        if ($needsReinit) {
            $this->calibrate(requireStatus: $this->calibrate_on_boot);
        }

        $ok = $this->send(AHTx0OpCode::CMD_TRIGGER->value, [$measurement_setting, $reserved]);

        if (! $ok) {
            $this->calibrate(requireStatus: $this->calibrate_on_boot);
            $ok = $this->send(AHTx0OpCode::CMD_TRIGGER->value, [$measurement_setting, $reserved]);
        }

        if (! $ok) {
            throw AHTx0Exception::commandWriteFailed(AHTx0OpCode::CMD_TRIGGER->value);
        }

        // Datasheet: ≥80ms conversion.
        usleep(100_000);

        if (! $this->shouldPollAfterMeasurementTrigger()) {
            // AHT30 returns status + data + CRC in one 7-byte transaction. A separate
            // status read consumes that result and leaves a status-only zero frame.
            return;
        }

        try {
            $this->waitUntilNotBusy(
                maxAttempts: 30,
                timeoutException: AHTx0Exception::measurementTimeout(),
            );
        } catch (AHTx0Exception) {
        }
    }

    /**
     * @return array{humidity: float, temperature: float, raw: array<int, int>}
     *
     * @throws AHTx0Exception
     */
    protected function pullMeasurement(): array
    {
        $data = null;
        $lastError = null;
        $responseLength = $this->measurementResponseLength();

        // Runtime evidence: first read after 0xAC often address-NACKs on USB MPSSE;
        // a later attempt succeeds (same pattern as status read retries).
        for ($attempt = 1; $attempt <= 15; $attempt++) {
            try {
                $data = $this->transport->read($responseLength);

                if (count($data) === $responseLength) {
                    break;
                }

                $lastError = AHTx0Exception::invalidResponseLength(count($data), $responseLength);
            } catch (AHTx0Exception $e) {
                $lastError = $e;
            }

            usleep(10_000);
        }

        if (is_null($data) || count($data) !== $responseLength) {
            throw $lastError ?? AHTx0Exception::invalidResponseLength(0, $responseLength);
        }

        $humidity = (($data[1] & 0xFF) << 12)
            | (($data[2] & 0xFF) << 4)
            | (($data[3] & 0xFF) >> 4);

        $temperature = (($data[3] & 0x0F) << 16)
            | (($data[4] & 0xFF) << 8)
            | ($data[5] & 0xFF);

        return [
            'humidity' => $this->compensateHumidity($humidity),
            'temperature' => $this->compensateTemperature($temperature),
            'raw' => array_values($data),
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
        return ($status & AHTx0StatusFlag::CALIBRATED->value) !== 0;
    }

    protected function isBusy(int $status): bool
    {
        return ($status & AHTx0StatusFlag::BUSY->value) !== 0;
    }

    protected function shouldPollAfterMeasurementTrigger(): bool
    {
        return true;
    }

    protected function measurementResponseLength(): int
    {
        return 6;
    }

    /**
     * @throws AHTx0Exception
     */
    protected function waitUntilNotBusy(int $maxAttempts, AHTx0Exception $timeoutException): int
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            usleep(10_000);

            try {
                $status = $this->readStatus();
            } catch (AHTx0Exception) {
                // Busy / bus blip: AHT often NACKs address during conversion. Retry.
                continue;
            }

            if (! $this->isBusy($status)) {
                return $status;
            }
        }

        throw $timeoutException;
    }

    /**
     * @throws AHTx0Exception
     */
    protected function _boot(): void
    {
        usleep(20_000);
        $this->reset();

        // Always initialize — skipping this leaves trigger (0xAC) NACKing on many parts.
        // `$calibrate_on_boot` only controls whether the calibrated status bit is required.
        $calibrated = $this->calibrate(requireStatus: $this->calibrate_on_boot);

        if (! $calibrated) {
            throw new AHTx0Exception('AHT sensor needs to be calibrated');
        }
    }
}
