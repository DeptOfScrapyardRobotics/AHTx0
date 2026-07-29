<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Enums;

enum AHTx0StatusFlag: int
{
    case BUSY = 0x80;

    case CALIBRATED = 0x08;
}
