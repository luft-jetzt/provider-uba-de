<?php declare(strict_types=1);

namespace App\SourceFetcher;

use App\Provider\UmweltbundesamtDe\SourceFetcher\QueryBuilder\QueryBuilder;
use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\Query\UbaCOQuery;
use App\SourceFetcher\Query\UbaNO2Query;
use App\SourceFetcher\Query\UbaO3Query;
use App\SourceFetcher\Query\UbaPM10Query;
use App\SourceFetcher\Query\UbaQueryInterface;
use App\SourceFetcher\Query\UbaSO2Query;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use GuzzleHttp\Client;

class SourceFetcher implements SourceFetcherInterface
{
    protected ParserInterface $parser;
    protected Client $client;

    public function __construct(ParserInterface $parser)
    {
        $this->parser = $parser;
        $this->client = new Client([
            'base_uri' => 'https://localhost:8000/',
            'verify' => false,
        ]);
    }

    public function fetch(): void
    {
        $endDateTime = new Carbon();
        $startDateTime = $endDateTime->sub(new CarbonInterval('P2H'));


    }

    protected function fetchPM10(Carbon $endDateTime, Carbon $startDateTime = null): void
    {
        $query = new UbaPM10Query();

        $this->fetchMeasurement($query, 1);
    }

    protected function fetchSO2(Carbon $endDateTime, Carbon $startDateTime = null): void
    {
        $query = new UbaSO2Query();

        $this->fetchMeasurement($query, 4);
    }

    protected function fetchNO2(Carbon $endDateTime, Carbon $startDateTime = null): void
    {
        $query = new UbaNO2Query();

        $this->fetchMeasurement($query, 3);
    }

    protected function fetchO3(Carbon $endDateTime, Carbon $startDateTime = null): void
    {
        $query = new UbaO3Query();

        $this->fetchMeasurement($query, 2);
    }

    protected function fetchCO(Carbon $endDateTime, Carbon $startDateTime = null): void
    {
        $query = new UbaCOQuery();

        $this->fetchMeasurement($query, 5);
    }

    protected function fetchMeasurement(UbaQueryInterface $query, int $pollutant): array
    {
        $responseString = $this->query($query);

        return $this->parser->parse($responseString, $pollutant);
    }

    protected function query(UbaQueryInterface $query): string
    {
        $data = QueryBuilder::buildQueryString($query);

        $queryString = sprintf('https://www.umweltbundesamt.de/api/air_data/v2/measures/json?%s', $data);

        $response = $this->client->get($queryString);

        return $response->getBody()->getContents();
    }
}
