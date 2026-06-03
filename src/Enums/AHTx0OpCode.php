<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Enums;

enum AHTx0OpCode: int
{
    case CMD_TRIGGER = 0xAC;

    case CMD_SOFT_RESET = 0xBA;
}
