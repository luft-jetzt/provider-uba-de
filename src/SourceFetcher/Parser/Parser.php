<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

use App\Model\Value\Value;
use Carbon\Carbon;

class Parser implements ParserInterface
{
    protected array $stationList;

    public function parse(string $responseString, int $pollutant): array
    {
        $response = json_decode($responseString);

        $this->fetchStationList();

        $valueList = [];

        foreach ($response['data'] as $stationId => $dataSet) {
            $data = array_pop($dataSet);

            if ($data[2] <= 0) {
                continue;
            }
            
            if (!array_key_exists($stationId, $this->stationList)) {
                continue;
            }

            $stationCode = $this->stationList[$stationId]->getStationCode();

            $dataValue = new Value();

            $dataValue
                ->setStationCode($stationCode)
                ->setDateTime(new Carbon($data[3]))
                ->setPollutant($pollutant)
                ->setValue($data[2]);

            $valueList[] = $dataValue;
        }

        return $valueList;
    }

    protected function fetchStationList(): Parser
    {
        $this->stationList = $this->registry->getRepository(Station::class)->findIndexedByProvider(UmweltbundesamtDeProvider::IDENTIFIER, 'ubaStationId');

        return $this;
    }
}
