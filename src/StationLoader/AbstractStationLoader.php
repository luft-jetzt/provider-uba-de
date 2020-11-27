<?php declare(strict_types=1);

namespace App\StationLoader;

use App\Api\StationApiInterface;

abstract class AbstractStationLoader implements StationLoaderInterface
{
    protected array $existingStationList = [];

    protected array $newStationList = [];

    protected array $changedStationList = [];

    protected StationApiInterface $stationApi;

    public function __construct(StationApiInterface $stationApi)
    {
        $this->stationApi = $stationApi;
    }

    public function getNewStationList(): array
    {
        return $this->newStationList;
    }

    protected function stationExists(string $stationCode): bool
    {
        return array_key_exists($stationCode, $this->existingStationList) || array_key_exists($stationCode, $this->newStationList);
    }
}
