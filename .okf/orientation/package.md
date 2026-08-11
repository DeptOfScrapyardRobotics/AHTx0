---
type: Module
title: Package (0.7)
description: dept-of-scrapyard-robotics/ahtx0 Composer identity, namespace, and discovery.
resource: composer.json
tags: [orientation, package, 0.7, ahtx0]
generated: { by: cursor-agent/grok-4.5, at: "2026-08-11T00:45:00Z" }
verified: { by: null, at: null }
status: draft
sources:
  - id: composer
    resource: composer.json
    title: Package composer.json
  - id: provider
    resource: src/AHTx0ServiceProvider.php
    title: AHTx0ServiceProvider
  - id: gitattributes
    resource: .gitattributes
    title: Dist export-ignore
---

# Identity

| Field | Value |
|-------|-------|
| Composer | `dept-of-scrapyard-robotics/ahtx0` **0.7.0** |
| PHP | `^8.4\|^8.5\|^8.6` |
| Namespace | `DeptOfScrapyardRobotics\Sensors\AHTx0\` → `src/` |
| Provider | `DeptOfScrapyardRobotics\Sensors\AHTx0\AHTx0ServiceProvider` (package root, not `Providers/`) |
| Catalog slugs | `aht10`, `aht20`, `aht30` |

# Requires

| Package | Constraint |
|---------|------------|
| `fabricate/nuts-and-bolts` | `^0.7.0` |
| `gpio/circuits` | `^0.7.0` |
| `gpio/contracts` | `^0.7.0` |
| `gpio/digital` | `^0.7.0` |
| `gpio/i2c` | `^0.7.0` |
| `waveforms/contracts` | `^0.7.0` |

**No** `scrapyard-io/tubes` — this is a sensor package, not a display driver.[^composer]

Suggested (optional): `microscrap/i2c`, `microscrap/mpsse` at `^0.7.0`.[^composer]

# Discovery

`extra.scrapyard-io.providers` lists `AHTx0ServiceProvider`. That provider registers the three catalog ICs, profile command, and `ahtx0-smoke` sketch.[^provider]

# Dist

`.okf/` and `AGENTS.md` are `export-ignore` — Composer dist tarballs omit them.[^gitattributes]

# Related

* [AHTx0 ICs](../core/ahtx0.md)
* [Circuits integration](../core/circuits.md)

[^composer]: Package composer.json
[^provider]: AHTx0ServiceProvider
[^gitattributes]: Dist export-ignore
