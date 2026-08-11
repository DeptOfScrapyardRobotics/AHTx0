<?php

namespace DeptOfScrapyardRobotics\Sensors\AHTx0\Enums;

enum AHTx0CatalogIc: string
{
    case AHT10 = 'aht10';
    case AHT20 = 'aht20';
    case AHT30 = 'aht30';

    /**
     * @return list<string>
     */
    public static function slugs(): array
    {
        return array_map(
            static fn (self $case): string => $case->value,
            self::cases(),
        );
    }
}
