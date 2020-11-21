<?php declare(strict_types=1);

namespace App\Api;

interface StationApiInterface
{
    public function getStations(): array;
}
