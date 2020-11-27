<?php declare(strict_types=1);

namespace App\SourceFetcher;

use App\SourceFetcher\QueryBuilder\QueryBuilder;
use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\Query\COQuery;
use App\SourceFetcher\Query\NO2Query;
use App\SourceFetcher\Query\O3Query;
use App\SourceFetcher\Query\PM10Query;
use App\SourceFetcher\Query\QueryInterface;
use App\SourceFetcher\Query\SO2Query;
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
        $query = new PM10Query();

        return $this->fetchMeasurement($query);
    }

    protected function fetchSO2(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new SO2Query();

        return $this->fetchMeasurement($query);
    }

    protected function fetchNO2(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new NO2Query();

        return $this->fetchMeasurement($query);
    }

    protected function fetchO3(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new O3Query();

        return $this->fetchMeasurement($query);
    }

    protected function fetchCO(Carbon $endDateTime, Carbon $startDateTime = null): string
    {
        $query = new COQuery();

        return $this->fetchMeasurement($query);
    }

    protected function fetchMeasurement(QueryInterface $query): string
    {
        return $this->query($query);
    }

    protected function query(QueryInterface $query): string
    {
        $data = QueryBuilder::buildQueryString($query);

        $queryString = sprintf('https://www.umweltbundesamt.de/api/air_data/v2/measures/json?%s', $data);

        $response = $this->client->get($queryString);

        return $response->getBody()->getContents();
    }
}
