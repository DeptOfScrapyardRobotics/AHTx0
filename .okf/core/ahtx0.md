---
type: Module
title: AHTx0 ICs
description: AHT10 / AHT20 / AHT30 SensorIC drivers — attributes, I2C factory, measurement API, chip quirks.
resource: src/
tags: [core, ic, sensor, i2c, aht10, aht20, aht30]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:45:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: aht10
    resource: src/AHT10/AHT10.php
    title: AHT10 class
  - id: aht20
    resource: src/AHT20/AHT20.php
    title: AHT20 class
  - id: aht30
    resource: src/AHT30/AHT30.php
    title: AHT30 class
  - id: transport
    resource: src/AHTx0CarrierTransport.php
    title: AHTx0CarrierTransport
  - id: internal-api
    resource: src/Concerns/AHTx0InternalAPI.php
    title: AHTx0InternalAPI (BootScaffolding)
  - id: aht30-api
    resource: src/AHT30/Concerns/AHT30API.php
    title: AHT30API measurement overrides
  - id: humidity-unit
    resource: src/Enums/HumidityUnit.php
    title: HumidityUnit
  - id: temperature-unit
    resource: src/Enums/TemperatureUnit.php
    title: TemperatureUnit
  - id: address
    resource: src/Enums/AHTx0I2CAddress.php
    title: AHTx0I2CAddress
---

# Role

I2C temperature + relative-humidity drivers for Aosong AHT10 / AHT20 / AHT30. Each class extends `GeneralPurposeIO\Circuits\SensorIC` and implements `BootSequence`.[^aht10][^aht20][^aht30]

# Attributes (all three ICs)

```php
#[IntegratedCircuit('I2C')]
#[Pinout(['I2C' => ['driver', 'device', 'slave']])]
```

Index-aligned with gpio-framework `circuit:make-profile` / package `ahtx0:make-profile` prompts.[^aht10]

# Factories

| Factory | Primary args | Notes |
|---------|--------------|-------|
| `::{i2c}($device, $adapter, $slave, $boot_now, $calibrate)` | device, adapter, slave (default `AHTx0I2CAddress::DEFAULT` = `0x38`) | Builds via `I2C::adapter` → `fromI2CBus` |
| `::fromI2CBus(I2CSlave $i2c, $boot_now, $calibrate)` | Already-open slave | Lower-level entry |

Defaults: `boot_now` true, `calibrate` true. Alternate address enum case: `0x39`.[^aht10][^address]

# Measurement surface

Shared via `AHTx0InternalAPI` (used by per-chip `AHT*API` traits):[^internal-api]

| Method / property | Meaning |
|-------------------|---------|
| `measureTemp(TemperatureUnit)` | Compensated temperature; unit convert via local enum |
| `measureHumidity(HumidityUnit)` | Compensated %RH (default `PERCENT`) |
| `getStatus()` / `$status` | Status byte |
| `$temperature` / `$relative_humidity` | `__get` → Celsius / %RH |
| `reset()` | Soft-reset `0xBA` (best-effort on clones) |
| `calibrate($requireStatus)` | Chip-specific initialize opcode + calibrate payload |

Magic properties throw `AHTx0Exception` (extends `CircuitException`) for unknown names.[^aht10]

# Boot

- Uses `GeneralPurposeIO\Contracts\Circuits\BootScaffolding` + Nab `Splices16Bits`.[^internal-api]
- `_boot()`: settle → soft-reset → initialize/calibrate. `$calibrate_on_boot` only controls whether the **calibrated status bit** is required — initialize is always sent (needed before `0xAC` trigger).
- Wire path: `AHTx0CarrierTransport` over `I2CSlave` only.[^transport]

# Chip differences

| Chip | Initialize opcode | Measurement response | Post-trigger status poll |
|------|-------------------|----------------------|--------------------------|
| AHT10 | `0xE1` | 6 bytes | yes |
| AHT20 | `0xBE` | 6 bytes | yes |
| AHT30 | `0xBE` | **7** bytes (status + data + CRC) | **no** — separate status read would consume the sample[^aht30-api] |

Shared opcodes: trigger `0xAC`, soft-reset `0xBA`. Status flags: busy `0x80`, calibrated `0x08`.

Empty measurement frames (status + five zero data bytes) are retried up to 3 times — seen on AHT30 after AHT20-style init.[^internal-api]

# Units

`HumidityUnit` and `TemperatureUnit` are **local** package enums — Fabricate sensor contracts are not in framework 0.7. See [Local unit enums](../traps/local-unit-enums.md).[^humidity-unit][^temperature-unit]

# Related

* [Circuits integration](circuits.md)
* [Package (0.7)](../orientation/package.md)
* [Fabricate leftovers](../traps/fabricate-leftovers.md)

[^aht10]: AHT10 class
[^aht20]: AHT20 class
[^aht30]: AHT30 class
[^transport]: AHTx0CarrierTransport
[^internal-api]: AHTx0InternalAPI (BootScaffolding)
[^aht30-api]: AHT30API measurement overrides
[^humidity-unit]: HumidityUnit
[^temperature-unit]: TemperatureUnit
[^address]: AHTx0I2CAddress
