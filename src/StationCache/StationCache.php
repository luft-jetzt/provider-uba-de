<?php declare(strict_types=1);

namespace App\StationCache;

use App\Model\Station;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class StationCache implements StationCacheInterface
{
    protected AdapterInterface $cache;

    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        $this->cache = new FilesystemAdapter(self::NAMESPACE, self::TTL);
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

        $station = $item->get();

        return $station;
    }
}

