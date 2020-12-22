<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

use App\StationManager\StationManagerInterface;
use Caldera\LuftApiBundle\Model\Station;
use Caldera\LuftApiBundle\Model\Value;
use Carbon\Carbon;

class Parser implements ParserInterface
{
    protected array $stationList;

    protected StationManagerInterface $stationManager;

    public function __construct(StationManagerInterface $stationManager)
    {
        $this->stationManager = $stationManager;
    }

    public function parse(string $responseString, string $pollutant): array
    {
        $response = json_decode($responseString, true, 512, JSON_OBJECT_AS_ARRAY);

        $valueList = [];

        dd($response['data']);

        foreach ($response['data'] as $ubaStationId => $dataSet) {
            $data = array_pop($dataSet);

            if ($data[2] <= 0) {
                continue;
            }

            if (!$this->stationManager->stationExists($ubaStationId)) {
                continue;
            }

            /** @var Station $station */
            $station = $this->stationManager->getStationById($ubaStationId);

            $value = new Value();

            $value
                ->setStationCode($station->getStationCode())
                ->setDateTime(new \DateTime($data[3]))
                ->setPollutant($pollutant)
                ->setValue($data[2]);

            $valueList[] = $value;
        }

        return $valueList;
    }
}
