<?php declare(strict_types=1);

namespace App\StationCache;

use Caldera\LuftModel\Model\Station;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class StationCache implements StationCacheInterface
{
    private readonly FilesystemAdapter $cache;

    public function __construct()
    {
        $this->cache = new FilesystemAdapter(self::NAMESPACE, self::TTL, self::CACHE_DIRECTORY);
    }

    public function addStation(Station $station): self
    {
        $key = (string) $station->getUbaStationId();

        $item = $this->cache->getItem($key);
        $item->set($station);
        $this->cache->save($item);

        return $this;
    }

    public function getStationByUbaStationId(int $ubaStationId): ?Station
    {
        $key = (string) $ubaStationId;

        $item = $this->cache->getItem($key);

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }
}
