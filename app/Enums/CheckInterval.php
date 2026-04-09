<?php

declare(strict_types=1);

namespace App\Enums;

enum CheckInterval: int
{
    case OneMinute = 1;
    case FiveMinutes = 5;
    case TenMinutes = 10;
    case ThirtyMinutes = 30;

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
