---
type: Trap
title: Fabricate leftovers
description: AHTx0 0.7 uses GeneralPurposeIO Circuits and local unit enums — not Fabricate Circuits or sensor contracts.
tags: [traps, fabricate, circuits, sensors]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:45:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: aht10
    resource: src/AHT10/AHT10.php
    title: AHT10 imports
  - id: internal-api
    resource: src/Concerns/AHTx0InternalAPI.php
    title: BootScaffolding + Nab Splices16Bits
  - id: provider
    resource: src/AHTx0ServiceProvider.php
    title: Circuit MagicAlias import
---

# Trap

Do **not** import or revive:

- `Fabricate\Contracts\Circuits\*`
- `Fabricate\Circuits\*` (including any old Fabricate boot / DataRegister paths)
- Fabricate sensor contract / unit types for humidity or temperature (not present in framework 0.7)

# Use instead

| Concern | Correct FQCN |
|---------|----------------|
| Taxonomy base | `GeneralPurposeIO\Circuits\SensorIC` |
| Attributes / BootSequence / BootScaffolding | `GeneralPurposeIO\Contracts\Circuits\Attributes\*`, `BootSequence`, `BootScaffolding` |
| Circuit alias | `GeneralPurposeIO\Core\MagicAliases\Circuit` |
| Bit helpers | `Fabricate\NutsAndBolts\Concerns\Splices16Bits` (Nab — OK) |
| Units | `DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\{HumidityUnit,TemperatureUnit}` |

`AHTx0` already wires GeneralPurposeIO Circuits types; BootScaffolding + Nab `Splices16Bits` are intentional.[^aht10][^internal-api][^provider]

This package does **not** depend on tubes.

# Related

* [AHTx0 ICs](../core/ahtx0.md)
* [Circuits integration](../core/circuits.md)
* [Local unit enums](local-unit-enums.md)

[^aht10]: AHT10 imports
[^internal-api]: BootScaffolding + Nab Splices16Bits
[^provider]: Circuit MagicAlias import
