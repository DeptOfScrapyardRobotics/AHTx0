Introduction
============

PHP Package for the AHTx0 family of temperature and relative humidity sensors.

Contains implementations for:
* AHT10
* AHT20
* AHT30

Compatible I2C Interfaces
===============
The AHTx0 family communicates with your device over I2C, the InterIntegrated Circuit Protocol.

You can interface with an AHTx0 with this package the following ways:
* A Linux Single-Board Computer's exposed GPIO pins using the dedicated I2C SDA/SCL pins
* An MPSSE-enabled USB-to-Serial device such as an FT232H generally using D0 and SCL and D1 for SDA connected to nearly any Linux or MacOS USB port.

Dependencies
=============
This package makes use of modules within:
* [The ScrapyardIO Framework](https://github.com/ScrapyardIO/framework)

This package also requires one of the following extensions in order to interface with I2C
* [POSI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/posi)
* [FTDI Extension v^0.4.0 or newer](https://github.com/php-io-extensions/ftdi)

In addition, an extension wrapper package is needed

For ext-posi
* [Microscrap POSIX Package v0.4.0 or newer](https://github.com/microscrap/posix)
* [Microscrap Native I2C Package v0.4.0 or newer](https://github.com/microscrap/gpio)

For ext-ftdi
* [Microscrap FTDI Package v0.4.0 or newer](https://github.com/microscrap/ftdi)
* [Microscrap MPSSE Package v0.4.0 or newer](https://github.com/microscrap/mpsse)

Installing from Composer
====================
Inside the root of your PHP Project, simply require the AHTx0 package from composer
```shell
composer require dept-of-scrapyard-robotics/ahtx0
```

Framework Configuration
====================

If you would like to use the ScrapyardIO Framework to bootstrap your sensor without
wasting lines configuring your sensor right in the script you can add your desired
configuration to scrapyard-io.php, such as in this example:
```php

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;

return [
    'boards' => [
        'aht20-native' => [
            'class_name' => AHT20::class,
            'connection' => [
                'driver' => 'native',
            ],
            'startup' => [
                'i2c' => [
                    'chip_device' => 1,
                    'slave_address' => AHTx0I2CAddress::DEFAULT->value,
                ],
            ],
        ],
        'aht20-usb' => [
            'class_name' => AHT20::class,
            'connection' => [
                'driver' => 'usb',
            ],
            'startup' => [
                'i2c' => [
                    'chip_device' => 'ft232h',
                    'slave_address' => AHTx0I2CAddress::DEFAULT->value,
                ],
            ],
        ],
    ],
];
```

The keys you choose to represent the integrated circuit's definition are up to you.

To bootstrap quickly using a ScrapyardIO `RelativeHumiditySensor` object you can start with the following:

```php

use RealityInterface\Sensors\Applied\Environmental\RelativeHumiditySensor;

$ahtx0 = RelativeHumiditySensor::using('aht20-native');

$humidity = $ahtx0->getHumidity();

```

Basic Usage
============
To use the sensor directly without the framework simply do the following:
```php
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;

$native_i2c_sensor = AHT20::connection('native')
    ->i2c(1, AHTx0I2CAddress::DEFAULT->value)
    ->create()
    
$temp_c = $native_i2c_sensor->temperature;
$rh = $native_i2c_sensor->humidity;

```
### USB (MPSSE) driver using I2C. (Linux and MacOS)
```php
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;

$usb_i2c_sensor = AHT20::connection('usb')
    ->i2c('ft232h', AHTx0I2CAddress::DEFAULT->value)
    ->create()
    
$temp_c = $usb_i2c_sensor->temperature;
$rh = $usb_i2c_sensor->humidity;
```


Advanced Usage
==============
You can select the sensor model class based on the board you wired while keeping the same I2C bootstrap pattern:
* `AHT10`
* `AHT20`
* `AHT30`

```php
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\AHT10;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\AHT30;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;

$aht10 = AHT10::connection('native')
    ->i2c(1, AHTx0I2CAddress::DEFAULT->value)
    ->create();

$aht20 = AHT20::connection('native')
    ->i2c(1, AHTx0I2CAddress::DEFAULT->value)
    ->create();

$aht30 = AHT30::connection('native')
    ->i2c(1, AHTx0I2CAddress::DEFAULT->value)
    ->create();
```

All three implementations expose the same core readings:
```php
$humidity = $aht20->relative_humidity;
$temperature = $aht20->temperature;
$status = $aht20->status;
```

Injected into the Framework
=========

To inject into the framework, that is, bootstrap the device directly then pass it off to a framework sensor object
simply do the following:

```php

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\Enums\AHTx0I2CAddress;
use RealityInterface\Sensors\Applied\Environmental\RelativeHumiditySensor;

$usb_sensor = AHT20::connection('usb')
    ->i2c('ft232h', AHTx0I2CAddress::DEFAULT->value)
    ->create();

$rh_sensor = RelativeHumiditySensor::as($usb_sensor);

$humidity = $rh_sensor->getHumidity();

```

Calibration
==========

To calibrate the sensor output in your application layer, you can use a trusted reference sensor:
* Bootstrap or inject the sensor into `RelativeHumiditySensor` or `TemperatureSensor`.
* Make sure your sensor and the reference sensor are sampling the same air volume.
* Pull measurements at the same time from both sensors.
* Use the framework calibration call to compute compensation.

Refer to this simulated example
```php

use RealityInterface\Sensors\Applied\Environmental\RelativeHumiditySensor;

$ahtx0 = RelativeHumiditySensor::using('aht20-native');

// Simulated out-of-scope event measuring both sensors at the same time
$current_humidity = $ahtx0->getHumidity();
$reference_humidity = (float) "your-reference-rh";

// Add the values from your calibration session here.
$ahtx0 = $ahtx0->calibrate($reference_humidity, $current_humidity);

// The output of getHumidity() will now be adjusted with your compensation factor
// computed from the calibration.
$adjusted_humidity = $ahtx0->getHumidity();

```

Sensor API
==========
The getters and property access in this API interface with the device directly (measurement trigger + read),
so you can use simple property access while still working against the chip itself.

Readable Properties (Getters)
-----------------------------
* `$sensor->relative_humidity`
  Reads and returns relative humidity in percent.

* `$sensor->temperature`
  Reads and returns temperature in Celsius.

* `$sensor->status`
  Reads and returns the current status register value.

Writable Properties (Setters)
-----------------------------
There are no writable magic properties exposed for AHTx0 sensors in this package.

Written by Angel Gonzalez for Project Saturn Studios, LLC.
