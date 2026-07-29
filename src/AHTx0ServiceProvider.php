<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0;

use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT10\AHT10;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\AHT20;
use DeptOfScrapyardRobotics\Sensors\AHTx0\AHT30\AHT30;
use Fabricate\NutsAndBolts\MagicAliases\Circuit;
use Fabricate\NutsAndBolts\ServiceProvider;

class AHTx0ServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Circuit::addCircuit('aht10', AHT10::class);
        Circuit::addCircuit('aht20', AHT20::class);
        Circuit::addCircuit('aht30', AHT30::class);
    }
}
