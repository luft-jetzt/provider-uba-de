<?php declare(strict_types=1);

namespace App\StationCache;

use App\Model\Station;

interface StationCacheInterface
{
    const TTL = 60 * 60;
    const NAMESPACE = 'luft_jetzt_uba_station_cache';

    public function addStation(Station $station): StationCacheInterface;

    public function getStationByUbaStationId(int $ubaStationId): ?Station;
}

