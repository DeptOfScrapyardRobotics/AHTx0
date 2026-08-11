# Agent guidelines — dept-of-scrapyard-robotics/ahtx0

## Knowledge Bundle (OKF)

This package ships an Open Knowledge Format bundle at [`.okf/`](.okf/) (excluded from Composer dist via `.gitattributes` `export-ignore`).

Before changing this package or advising on AHTx0 architecture:

1. Read [`.okf/index.md`](.okf/index.md) first (progressive disclosure).
2. Open only the linked concepts needed for the task.
3. Prefer `status: stable` concepts; treat `deprecated` as historical only. New/changed concepts stay `status: draft` until a human verifies them.
4. When you learn something durable about **this package**, update the affected `.okf` concept(s) and append `.okf/log.md`.
5. Keep the `.okf` bundle at the **package root** only — do not nest extra `.okf` folders under `src/`.
6. Circuits registry semantics belong in `scrapyard-io/gpio-framework`’s `.okf`. Do not pull tubes/display knowledge into this sensor package.

## Package rules (quick) — 0.7.x

- Composer: `dept-of-scrapyard-robotics/ahtx0` **0.7.0**. Namespace `DeptOfScrapyardRobotics\Sensors\AHTx0\`.
- Provider: `AHTx0ServiceProvider` at package root. Catalog slugs `aht10` / `aht20` / `aht30`. Command `ahtx0:make-profile` (delegated from `circuit:make-profile`). Sketch `ahtx0-smoke`.
- Requires leaf components (not kitchen-sink frameworks): `fabricate/nuts-and-bolts`, `gpio/circuits`, `gpio/contracts`, `gpio/digital`, `gpio/i2c`, `waveforms/contracts`.
- ICs extend `GeneralPurposeIO\Circuits\SensorIC`, implement `BootSequence`; factory `i2c(device, adapter, slave, boot_now, calibrate)`.
- Attributes: `#[IntegratedCircuit('I2C')]` + `#[Pinout(['I2C' => ['driver', 'device', 'slave']])]`.
- Boot uses `BootScaffolding`; bit helpers use Nab `Splices16Bits`. Units are local `HumidityUnit` / `TemperatureUnit` enums (no Fabricate sensor contracts in framework 0.7).
- Never import `Fabricate\Contracts\Circuits\*` or `Fabricate\Circuits\*`.
