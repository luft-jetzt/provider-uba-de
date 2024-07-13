<?php declare(strict_types=1);

namespace App\StationCache;

use Caldera\LuftModel\Model\Station;

interface StationCacheInterface
{
    final public const int  TTL = 60 * 60;
    final public const string NAMESPACE = 'luft_jetzt-uba_station_cache';
    final public const string CACHE_DIRECTORY = __DIR__.'/../../../var/cache/';

    public function addStation(Station $station): StationCacheInterface;

    public function getStationByUbaStationId(int $ubaStationId): ?Station;
}

