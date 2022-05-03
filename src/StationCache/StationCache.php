<?php declare(strict_types=1);

namespace App\StationCache;

use Caldera\LuftModel\Model\Station;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class StationCache implements StationCacheInterface
{
    protected AdapterInterface $cache;

    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;

        $client = RedisAdapter::createConnection(
            'redis://localhost:6379'
        );

        $this->cache = new RedisAdapter($client, self::NAMESPACE, self::TTL);
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
