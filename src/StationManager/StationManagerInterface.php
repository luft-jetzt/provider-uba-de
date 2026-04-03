<?php declare(strict_types=1);

namespace App\StationManager;

use Caldera\LuftModel\Model\Station;

interface StationManagerInterface
{
    /** @return list<\Caldera\LuftModel\Model\Station> */
    public function loadStationList(): array;

    /** @param list<\Caldera\LuftModel\Model\Station> $stationList */
    public function cacheStationList(array $stationList): void;
    public function stationExists(int $ubaStationId): bool;
    public function getStationById(int $ubaStationId): ?Station;
}

