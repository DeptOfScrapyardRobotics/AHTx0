---
okf_version: "0.2"
---

# dept-of-scrapyard-robotics/ahtx0 Knowledge Bundle

Package knowledge for `dept-of-scrapyard-robotics/ahtx0` (AHT10 / AHT20 / AHT30 temp+RH sensors, v0.7.x).
Read this index first; open only the concepts needed for the task.

**Trust rule:** Prefer `status: stable`. Treat `deprecated` as historical only. New agent-written concepts stay `status: draft` until a human verifies them.
**Placement:** Package-root `.okf/` only — never under `src/`.
**Links:** Concept cross-links use paths relative to each file.
**Scope:** This package’s IC surface, Circuits catalog registration, profiles, and smoke sketch. Registry semantics live in `scrapyard-io/gpio-framework` — do not duplicate that bundle here. No tubes dependency.
**Dist note:** `.okf/` and root `AGENTS.md` are `export-ignore` in `.gitattributes`.

# Orientation

* [Package (0.7)](orientation/package.md) - Composer identity, namespace, provider, dependencies.

# Core

* [AHTx0 ICs](core/ahtx0.md) - SensorIC classes, attributes, I2C factory, measurement API, AHT30 quirks.
* [Circuits integration](core/circuits.md) - Catalog slugs, make-profile, profiles, smoke sketch.

# Traps

* [Fabricate leftovers](traps/fabricate-leftovers.md) - GeneralPurposeIO Circuits + local unit enums; not Fabricate Circuits/sensor contracts.
* [Local unit enums](traps/local-unit-enums.md) - HumidityUnit / TemperatureUnit live in this package (framework 0.7 has no sensor contracts).

# Log

* [Directory update log](log.md)
