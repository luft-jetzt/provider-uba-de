<?php declare(strict_types=1);

namespace App\StationManager;

use App\StationCache\StationCacheInterface;
use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftModel\Model\Station;
use JMS\Serializer\SerializerInterface;

class StationManager implements StationManagerInterface
{
    protected StationCacheInterface $stationCache;
    protected SerializerInterface $serializer;
    protected StationApiInterface $stationApi;

    public function __construct(SerializerInterface $serializer, StationApiInterface $stationApi, StationCacheInterface $stationCache)
    {
        $this->serializer = $serializer;
        $this->stationApi = $stationApi;
        $this->stationCache = $stationCache;
    }

    public function loadStationList(): array
    {
        return $this->stationApi->getStations('uba_de');
    }

    public function cacheStationList(array $stationList): void
    {
        foreach ($stationList as $station) {
            $this->stationCache->addStation($station);
        }
    }

    public function stationExists(int $ubaStationId): bool
    {
        return $this->getStationById($ubaStationId) !== null;
    }

    public function getStationById(int $ubaStationId): ?Station
    {
        return $this->stationCache->getStationByUbaStationId($ubaStationId);
    }
}

