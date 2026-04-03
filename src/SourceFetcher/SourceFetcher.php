<?php declare(strict_types=1);

namespace App\SourceFetcher;

use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\Query\Query;
use App\SourceFetcher\QueryBuilder\QueryBuilder;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SourceFetcher implements SourceFetcherInterface
{
    private const API_URL = 'https://www.umweltbundesamt.de/api/air_data/v2/measures/json';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function fetch(Pollutant $pollutant, ?\DateTimeImmutable $untilDateTime = null, ?\DateTimeImmutable $fromDateTime = null): string
    {
        if (!$untilDateTime) {
            $untilDateTime = new \DateTimeImmutable();
        }

        if (!$fromDateTime) {
            $fromDateTime = $untilDateTime->sub(new \DateInterval('PT2H'));
        }

        $query = new Query($pollutant, $untilDateTime, $fromDateTime);

        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => QueryBuilder::buildQueryParameters($query),
        ]);

        return $response->getContent();
    }
}
