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

class SourceFetcher implements SourceFetcherInterface
{
    public function __construct(protected readonly ParserInterface $parser)
    {

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
        $query = new PM10Query($untilDateTime, $fromDateTime);

        return $this->fetchMeasurement($query);
    }

    protected function fetchSO2(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        $query = new SO2Query($untilDateTime, $fromDateTime);

        return $this->fetchMeasurement($query);
    }

    protected function fetchNO2(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        $query = new NO2Query($untilDateTime, $fromDateTime);

        return $this->fetchMeasurement($query);
    }

    protected function fetchO3(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        $query = new O3Query($untilDateTime, $fromDateTime);

        return $this->fetchMeasurement($query);
    }

    protected function fetchCO(\DateTimeImmutable $untilDateTime, ?\DateTimeImmutable $fromDateTime = null): string
    {
        $query = new COQuery($untilDateTime, $fromDateTime);

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

        $response = file_get_contents($queryString);

        return $response;
    }
}
