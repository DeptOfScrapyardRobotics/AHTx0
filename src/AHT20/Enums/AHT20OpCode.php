<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\AHT20\Enums;

enum AHT20OpCode: int
{
    case CMD_INITIALIZE = 0xBE;

    case CMD_TRIGGER = 0xAC;

    case CMD_SOFT_RESET = 0xBA;
}
