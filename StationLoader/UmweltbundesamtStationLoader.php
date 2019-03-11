<?php declare(strict_types=1);

namespace App\Provider\UmweltbundesamtDe\StationLoader;

use App\Entity\Station;
use App\Provider\AbstractStationLoader;
use App\Provider\StationLoaderInterface;
use App\Provider\UmweltbundesamtDe\UmweltbundesamtDeProvider;
use Curl\Curl;
use Doctrine\ORM\EntityManager;
use League\Csv\Reader;

class UmweltbundesamtStationLoader extends AbstractStationLoader
{
    const SOURCE_URL = 'https://www.env-it.de/stationen/public/download.do?event=euMetaStation';

    /** @var Reader $csv */
    protected $csv;

    /** @var bool $update */
    protected $update = false;

    public function process(callable $callback): StationLoaderInterface
    {
        /** @var EntityManager $em */
        $em = $this->registry->getManager();

        foreach ($this->csv as $stationData) {
            $callback();

            if (!$stationData['station_code']) {
                continue;
            } elseif (!$this->stationExists($stationData['station_code'], $this->existingStationList)) {
                $station = $this->createStation($stationData);

                $em->merge($station);

                $this->newStationList[] = $station;
            } elseif ($this->update === true) {
                $station = $this->existingStationList[$stationData['station_code']];

                $station = $this->mergeStation($station, $stationData);
            }
        }

        $em->flush();

        return $this;
    }

    protected function mergeStation(Station $station, array $stationData): Station
    {
        $station
            ->setTitle($stationData['station_name'])
            ->setProvider(UmweltbundesamtDeProvider::IDENTIFIER)
            ->setStationCode($stationData['station_code'])
            ->setLatitude(floatval($stationData['station_latitude_d']))
            ->setLongitude(floatval($stationData['station_longitude_d']))
            ->setFromDate($this->parseDate($stationData['station_start_date']))
            ->setUntilDate(!empty($stationData['station_end_date']) ? $this->parseDate($stationData['station_end_date']) :null)
            ->setAltitude(intval($stationData['station_altitude']))
            ->setStationType(!empty($stationData['type_of_station']) ? $stationData['type_of_station'] : null)
            ->setAreaType(!empty($stationData['station_type_of_area']) ? $stationData['station_type_of_area'] : null);

        return $station;
    }

    public function load(): StationLoaderInterface
    {
        $this->existingStationList = $this->getExistingStationList('uba_de');

        $this->csv = $this->fetchStationList();

        $this->csv
            ->setDelimiter(';')
            ->setHeaderOffset(1);

        return $this;
    }

    public function count(): int
    {
        return $this->csv ? $this->csv->count() : 0;
    }

    public function setUpdate(bool $update = false): StationLoaderInterface
    {
        $this->update = $update;

        return $this;
    }

    protected function fetchStationList(): Reader
    {
        $curl = new Curl();
        $curl->get(self::SOURCE_URL);

        $csv = Reader::createFromString(utf8_decode($curl->response));

        return $csv;
    }

    protected function parseStateCode(string $stationCode): string
    {
        return substr($stationCode, 2, 2);
    }

    protected function parseDate(string $dateString): \DateTime
    {
        sscanf($dateString,'%4d%2d%2d', $year, $month, $day);

        return new \DateTime(sprintf('%d-%d-%d', $year, $month, $day));
    }

    protected function createStation(array $stationData): Station
    {
        $station = new Station(floatval($stationData['station_latitude_d']), floatval($stationData['station_longitude_d']));

        $this->mergeStation($station, $stationData);

        return $station;
    }

    public function getExistingStationList(): array
    {
        return $this->registry->getRepository(Station::class)->findIndexedByProvider(UmweltbundesamtDeProvider::IDENTIFIER);
    }
}
