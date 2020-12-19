<?php declare(strict_types=1);

namespace App\StationLoader;

use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftApiBundle\Model\Station;

class StationLoader implements StationLoaderInterface
{
    const SOURCE_URL = 'https://www.umweltbundesamt.de/api/air_data/v2/meta/json?use=measure&lang=de';

    const PROVIDER_IDENTIFIER = 'uba_de';

    const FIELD_ID = 0;
    const FIELD_STATION_CODE = 1;
    const FIELD_TITLE = 2;
    const FIELD_CITY = 3;
    const FIELD_START_DATE = 5;
    const FIELD_LONGITUDE = 7;
    const FIELD_LATITUDE = 8;
    const FIELD_STATE_CODE = 12;
    const FIELD_STATE = 13;
    const FIELD_AREA_TYPE = 15;
    const FIELD_STATION_TYPE = 16;

    protected bool $update = false;

    protected array $ubaStationList = [];

    protected StationApiInterface $stationApi;

    public function __construct(StationApiInterface $stationApi)
    {
        $this->stationApi = $stationApi;
    }

    protected function stationExists(StationLoadResult $stationLoadResult, string $stationCode): bool
    {
        return array_key_exists($stationCode, $stationLoadResult->getNewStationList()) || array_key_exists($stationCode, $stationLoadResult->getChangedStationList()) || array_key_exists($stationCode, $stationLoadResult->getExistingStationList());
    }

    protected function mergeStation(Station $station, array $stationData): Station
    {
        $station
            ->setTitle($stationData[self::FIELD_TITLE])
            ->setProvider('uba_de')
            ->setStationCode($stationData[self::FIELD_STATION_CODE])
            ->setLatitude((float)$stationData[self::FIELD_LATITUDE])
            ->setLongitude((float)$stationData[self::FIELD_LONGITUDE])
            ->setFromDate(new \DateTime($stationData[self::FIELD_START_DATE]))
            ->setStationType($this->mapStationType($stationData[self::FIELD_STATION_TYPE]))
            ->setAreaType($this->mapAreaType($stationData[self::FIELD_AREA_TYPE]))
            ->setUbaStationId((int)$stationData[self::FIELD_ID]);

        return $station;
    }

    public function load(): StationLoadResult
    {
        $existingStationList = $this->getExistingStationList();
        $stationLoadResult = new StationLoadResult();
        $stationLoadResult->setExistingStationList($existingStationList);

        $this->fetchStationList();

        foreach ($this->ubaStationList as $stationData) {
            if (!array_key_exists(self::FIELD_STATION_CODE, $stationData) || !$stationData[self::FIELD_STATION_CODE]) {
                continue;
            }

            $stationCode = $stationData[self::FIELD_STATION_CODE];

            if (!$this->stationExists($stationLoadResult, $stationCode)) {
                $station = $this->createStation($stationData);

                $stationLoadResult->addNewStation($station);
            } elseif ($this->update === true) {
                $station = $this->existingStationList[$stationCode];

                $this->mergeStation($station, $stationData);

                $stationLoadResult->addChangedStation($station);
            }
        }

        return $stationLoadResult;
    }

    public function setUpdate(bool $update = false): StationLoaderInterface
    {
        $this->update = $update;

        return $this;
    }

    protected function fetchStationList(): array
    {
        $csvFileContent = file_get_contents(self::SOURCE_URL);

        $this->ubaStationList = (json_decode($csvFileContent, true))['stations'];

        return $this->ubaStationList;
    }

    protected function createStation(array $stationData): Station
    {
        $latitude = (float)$stationData[self::FIELD_LATITUDE];
        $longitude = (float)$stationData[self::FIELD_LONGITUDE];

        $station = new Station();
        $station
            ->setLatitude($latitude)
            ->setLongitude($longitude);

        $this->mergeStation($station, $stationData);

        return $station;
    }

    public function getExistingStationList(): array
    {
        return $this->stationApi->getStations();
    }

    protected function mapAreaType(string $areaType): string
    {
        switch ($areaType) {
            case 'vorstädtisch':
                return 'suburban';
            case 'städtisch':
                return 'urban';
            case 'ländlich':
                return 'rural';
        }
    }

    protected function mapStationType(string $stationType): string
    {
        switch ($stationType) {
            case 'Hintergrund':
                return 'background';
            case 'Verkehr':
                return 'traffic';
            case 'Industrie':
                return 'industrial';
        }
    }
}
