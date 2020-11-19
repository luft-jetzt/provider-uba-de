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

    public function fetch(): string
    {
        $endDateTime = new Carbon();
        $startDateTime = $endDateTime->sub(new CarbonInterval('PT2H'));

        return $this->fetchNO2($endDateTime, $startDateTime);
    }

    protected function fetchPM10(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new UbaPM10Query();

        return $this->fetchMeasurement($query, 1);
    }

    protected function fetchSO2(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new UbaSO2Query();

        return $this->fetchMeasurement($query, 4);
    }

    protected function fetchNO2(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new UbaNO2Query();

        return $this->fetchMeasurement($query, 3);
    }

    protected function fetchO3(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new UbaO3Query();

        return $this->fetchMeasurement($query, 2);
    }

    protected function fetchCO(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new UbaCOQuery();

        return $this->fetchMeasurement($query, 5);
    }

    protected function fetchMeasurement(UbaQueryInterface $query, int $pollutant): string
    {
        return $this->query($query);
    }

    protected function query(UbaQueryInterface $query): string
    {
        $data = QueryBuilder::buildQueryString($query);

        $queryString = sprintf('https://www.umweltbundesamt.de/api/air_data/v2/measures/json?%s', $data);

        $response = $this->client->get($queryString);

        return $response->getBody()->getContents();
    }
}
