---
type: Trap
title: Local unit enums
description: HumidityUnit and TemperatureUnit are package-local — do not expect Fabricate sensor contracts in framework 0.7.
tags: [traps, enums, sensors, units]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:45:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: humidity
    resource: src/Enums/HumidityUnit.php
    title: HumidityUnit
  - id: temperature
    resource: src/Enums/TemperatureUnit.php
    title: TemperatureUnit
  - id: internal-api
    resource: src/Concerns/AHTx0InternalAPI.php
    title: measureTemp / measureHumidity
---

# Trap

Do **not** import fictional or deferred Fabricate sensor contracts such as shared `TemperatureUnit` / `HumidityUnit` from framework Core or Contracts — they are **not** in `scrapyard-io/framework` 0.7.

# Use instead

Package-local string-backed enums:[^humidity][^temperature]

| Enum | Cases |
|------|-------|
| `DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\HumidityUnit` | `PERCENT = '%'` |
| `DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\TemperatureUnit` | `CELSIUS = 'C'`, `FAHRENHEIT = 'F'` |

`measureTemp` / `measureHumidity` take these enums and convert via `::convert()`.[^internal-api]

If shared sensor contracts return later in framework, migrate call sites deliberately — do not invent cross-package FQCNs ahead of that.

# Related

* [AHTx0 ICs](../core/ahtx0.md)
* [Fabricate leftovers](fabricate-leftovers.md)

[^humidity]: HumidityUnit
[^temperature]: TemperatureUnit
[^internal-api]: measureTemp / measureHumidity
