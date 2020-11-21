<?php declare(strict_types=1);

namespace App\StationManager;

use App\Api\StationApiInterface;
use App\StationCache\StationCacheInterface;
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

    public function loadStationList(): StationManager
    {
        $this->stationApi->getStations();

        return $this;
    }
}

