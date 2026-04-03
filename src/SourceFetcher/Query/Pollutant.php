<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

enum Pollutant: string
{
    case PM10 = 'pm10';
    case NO2 = 'no2';
    case O3 = 'o3';
    case CO = 'co';
    case SO2 = 'so2';

    public function component(): int
    {
        return match ($this) {
            self::PM10 => 1,
            self::CO => 2,
            self::O3 => 3,
            self::SO2 => 4,
            self::NO2 => 5,
        };
    }

    public function scope(): int
    {
        return match ($this) {
            self::CO => 4,
            default => 2,
        };
    }
}
