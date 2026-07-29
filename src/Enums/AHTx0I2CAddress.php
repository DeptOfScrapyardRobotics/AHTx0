<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Enums;

enum AHTx0I2CAddress: int
{
    case DEFAULT = 0x38;

    case ALTERNATE = 0x39;
}
