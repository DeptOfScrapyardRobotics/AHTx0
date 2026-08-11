# dept-of-scrapyard-robotics/ahtx0 (0.7)

I2C drivers for AHT10 / AHT20 / AHT30 temperature & humidity sensors. Extends `GeneralPurposeIO\Circuits\SensorIC`.

## Register

Provider registers catalog slugs `aht10`, `aht20`, `aht30` and wires `ahtx0:make-profile` into `circuit:make-profile`.

## Profiles

```bash
workshop vendor:publish --tag=gpio-circuits-config
workshop circuit:make-profile          # picks any installed IC; AHTx0 delegates here
workshop ahtx0:make-profile            # AHT10 / AHT20 / AHT30 only
```

The command asks I2C `driver` / `device` / `slave` from `#[Pinout]`, and always sets `boot_now => true`.

```php
Circuit::profile('climate_lab');
```

## Smoke sketch

Requires at least one AHTx0 profile in `config/circuits.php`:

```bash
php workshop runner ahtx0-smoke
php workshop runner ahtx0-smoke --profile=climate_lab
```

Provisions only via `Circuit::profile()` — samples °C / %RH until you Ctrl-C.
