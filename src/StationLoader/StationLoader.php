<?php declare(strict_types=1);

namespace App\StationLoader;

use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftModel\Model\Station;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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

    /** @var array<int, array<int, mixed>> */
    protected array $ubaStationList = [];

    public function __construct(
        protected StationApiInterface $stationApi,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    protected function stationExists(StationLoadResult $stationLoadResult, string $stationCode): bool
    {
        return array_key_exists($stationCode, $stationLoadResult->getNewStationList()) || array_key_exists($stationCode, $stationLoadResult->getChangedStationList()) || array_key_exists($stationCode, $stationLoadResult->getExistingStationList());
    }

    // Bounding box covering Germany (with a small margin). Coordinates outside
    // this range indicate corrupt UBA meta data and are rejected before the
    // station is submitted to the luft.jetzt API.
    private const GERMANY_LATITUDE_MIN = 47.0;
    private const GERMANY_LATITUDE_MAX = 55.5;
    private const GERMANY_LONGITUDE_MIN = 5.5;
    private const GERMANY_LONGITUDE_MAX = 15.5;

    private function assertPlausibleCoordinates(float $latitude, float $longitude, string $stationCode): void
    {
        if (
            $latitude < self::GERMANY_LATITUDE_MIN || $latitude > self::GERMANY_LATITUDE_MAX
            || $longitude < self::GERMANY_LONGITUDE_MIN || $longitude > self::GERMANY_LONGITUDE_MAX
        ) {
            throw new \InvalidArgumentException(sprintf(
                'Implausible coordinates for station "%s": latitude %s, longitude %s (expected within Germany).',
                $stationCode,
                $latitude,
                $longitude,
            ));
        }
    }

    /** @param array<int, mixed> $stationData */
    protected function mergeStation(Station $station, array $stationData): Station
    {
        $latitude = (float) $stationData[self::FIELD_LATITUDE];
        $longitude = (float) $stationData[self::FIELD_LONGITUDE];

        $this->assertPlausibleCoordinates($latitude, $longitude, (string) $stationData[self::FIELD_STATION_CODE]);

        $station
            ->setTitle($stationData[self::FIELD_TITLE])
            ->setProvider('uba_de')
            ->setStationCode($stationData[self::FIELD_STATION_CODE])
            ->setLatitude($latitude)
            ->setLongitude($longitude)
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
                $station = $stationLoadResult->getExistingStationList()[$stationCode];

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

    /** @return array<int, array<int, mixed>> */
    protected function fetchStationList(): array
    {
        $response = $this->httpClient->request('GET', self::SOURCE_URL);
        $data = $response->toArray();

        $this->ubaStationList = $data['stations'];

        return $this->ubaStationList;
    }

    /** @param array<int, mixed> $stationData */
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

    /** @return array<string, Station> */
    public function getExistingStationList(): array
    {
        return $this->stationApi->getStations();
    }

    protected function mapAreaType(string $areaType): string
    {
        return match ($areaType) {
            'vorstädtisch' => 'suburban',
            'städtisch' => 'urban',
            'ländlich' => 'rural',
            default => throw new \InvalidArgumentException(sprintf('Unknown area type: %s', $areaType)),
        };
    }

    protected function mapStationType(string $stationType): string
    {
        return match ($stationType) {
            'Hintergrund' => 'background',
            'Verkehr' => 'traffic',
            'Industrie' => 'industrial',
            default => throw new \InvalidArgumentException(sprintf('Unknown station type: %s', $stationType)),
        };
    }
}
