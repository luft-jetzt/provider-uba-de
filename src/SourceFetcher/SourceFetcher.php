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
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SourceFetcher implements SourceFetcherInterface
{
    private const API_URL = 'https://www.umweltbundesamt.de/api/air_data/v2/measures/json';

    public function __construct(
        protected readonly ParserInterface $parser,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function fetch(string $pollutantIdentifier, ?\DateTimeImmutable $untilDateTime = null, ?\DateTimeImmutable $fromDateTime = null): string
    {
        if (!$untilDateTime) {
            $untilDateTime = new \DateTimeImmutable();
        }

        if (!$fromDateTime) {
            $fromDateTime = $untilDateTime->sub(new \DateInterval('PT2H'));
        }

        $fetchMethodName = sprintf('fetch%s', strtoupper($pollutantIdentifier));
        return $this->$fetchMethodName($untilDateTime, $fromDateTime);
    }

    protected function fetchPM10(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        return $this->fetchMeasurement(new PM10Query($untilDateTime, $fromDateTime));
    }

    protected function fetchSO2(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        return $this->fetchMeasurement(new SO2Query($untilDateTime, $fromDateTime));
    }

    protected function fetchNO2(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        return $this->fetchMeasurement(new NO2Query($untilDateTime, $fromDateTime));
    }

    protected function fetchO3(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        return $this->fetchMeasurement(new O3Query($untilDateTime, $fromDateTime));
    }

    protected function fetchCO(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        return $this->fetchMeasurement(new COQuery($untilDateTime, $fromDateTime));
    }

    protected function fetchMeasurement(QueryInterface $query): string
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => QueryBuilder::buildQueryParameters($query),
        ]);

        return $response->getContent();
    }
}
