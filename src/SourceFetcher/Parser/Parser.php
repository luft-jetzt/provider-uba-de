<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

use App\StationManager\StationManagerInterface;
use Caldera\LuftModel\Model\Station;
use Caldera\LuftModel\Model\Value;

class Parser implements ParserInterface
{
    /** @var array<int, Station> */
    protected array $stationList;

    /**
     * Number of measurements skipped during the last parse() run because their
     * station was not present in the station cache (cache miss). Exposed so the
     * caller can make the otherwise silent data loss observable.
     */
    private int $skippedStationValueCount = 0;

    public function __construct(protected readonly StationManagerInterface $stationManager)
    {
    }

    /** @return list<Value> */
    public function parse(string $responseString, string $pollutant): array
    {
        $this->skippedStationValueCount = 0;

        try {
            $response = json_decode($responseString, true, 512, JSON_THROW_ON_ERROR | JSON_OBJECT_AS_ARRAY);
        } catch (\JsonException $exception) {
            throw new \RuntimeException(sprintf('Failed to decode UBA response as JSON: %s', $exception->getMessage()), 0, $exception);
        }

        if (!is_array($response) || !array_key_exists('data', $response) || !is_array($response['data'])) {
            throw new \RuntimeException('Unexpected UBA response: missing or invalid "data" key.');
        }

        $valueList = [];

        foreach ($response['data'] as $ubaStationId => $dataSet) {
            while ($data = array_pop($dataSet)) {
                if ($data[2] <= 0) {
                    continue;
                }

                if (!$this->stationManager->stationExists($ubaStationId)) {
                    ++$this->skippedStationValueCount;

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
        }

        return $valueList;
    }

    /**
     * Number of measurements dropped in the last parse() run because their
     * station was not found in the cache.
     */
    public function getSkippedStationValueCount(): int
    {
        return $this->skippedStationValueCount;
    }
}
