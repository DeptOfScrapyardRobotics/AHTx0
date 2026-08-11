---
type: Module
title: Circuits integration
description: Catalog registration, ahtx0:make-profile, profiles, and ahtx0-smoke sketch.
resource: src/AHTx0ServiceProvider.php
tags: [circuits, catalog, profile, smoke, workshop]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:45:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: provider
    resource: src/AHTx0ServiceProvider.php
    title: AHTx0ServiceProvider
  - id: catalog
    resource: src/Enums/AHTx0CatalogIc.php
    title: AHTx0CatalogIc
  - id: console-enum
    resource: src/Enums/AHTx0ConsoleCommand.php
    title: AHTx0ConsoleCommand
  - id: make-profile
    resource: src/Console/AHTx0MakeProfileCommand.php
    title: ahtx0:make-profile
  - id: smoke
    resource: src/Sketches/AHTx0Smoke.php
    title: ahtx0-smoke
---

# Role

This package **owns the AHTx0 chip drivers** and registers them with gpio-framework Circuits. Registry / fluent / profile **semantics** live in `scrapyard-io/gpio-framework` — open that package’s `.okf` for `CircuitRegistry`, `PendingCircuit`, and `circuit:make-profile` behavior.

# Catalog

On `boot()`:[^provider]

```php
Circuit::addCircuit(AHTx0CatalogIc::AHT10->value, AHT10::class); // 'aht10'
Circuit::addCircuit(AHTx0CatalogIc::AHT20->value, AHT20::class); // 'aht20'
Circuit::addCircuit(AHTx0CatalogIc::AHT30->value, AHT30::class); // 'aht30'

$maker = AHTx0ConsoleCommand::MAKE_PROFILE->value; // 'ahtx0:make-profile'
foreach (AHTx0CatalogIc::cases() as $ic) {
    Circuit::registerProfileCommand($ic->value, $maker);
}
```

Slug enum: `AHTx0CatalogIc` cases `aht10` / `aht20` / `aht30`.[^catalog][^console-enum]

# Profiles

Publish gpio Circuits config first (from gpio-framework), then scaffold:

```bash
workshop vendor:publish --tag=gpio-circuits-config
workshop circuit:make-profile          # picks any installed IC; AHTx0 delegates here
workshop ahtx0:make-profile            # AHT10 / AHT20 / AHT30 only
```

`ahtx0:make-profile` uses `ScaffoldsCircuitProfiles` + `CircuitAttributeInspector` — prompts I2C `driver` / `device` / `slave` from `#[Pinout]`, writes `config/circuits.php` with `boot_now => true`.[^make-profile]

```php
Circuit::profile('climate_lab'); // recipe ic => aht10|aht20|aht30
```

# Smoke sketch

Sketch slug: `ahtx0-smoke` (`#[SketchAttribute('ahtx0-smoke')]`), registered when `SketchRegistry` is bound.[^provider][^smoke]

```bash
php workshop runner ahtx0-smoke
php workshop runner ahtx0-smoke --profile=climate_lab
```

Requires at least one profile whose `ic` is `aht10` / `aht20` / `aht30`. Provisions **only** via `Circuit::profile()` — samples °C / %RH (~1 Hz) until Ctrl-C; closes the sensor on shutdown.[^smoke]

# Related

* [AHTx0 ICs](ahtx0.md)
* [Package (0.7)](../orientation/package.md)

[^provider]: AHTx0ServiceProvider
[^catalog]: AHTx0CatalogIc
[^console-enum]: AHTx0ConsoleCommand
[^make-profile]: ahtx0:make-profile
[^smoke]: ahtx0-smoke
