<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher;

use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\SourceFetcher;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SourceFetcherTest extends TestCase
{
    public function testFetchDefaultsToTwoHourRange(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"data":{}}');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $this->anything(), $this->callback(function (array $options): bool {
                $query = $options['query'];
                $timeFrom = $query['time_from'];
                $timeTo = $query['time_to'];
                // The time range should span roughly 2 hours (+1 offset each)
                return isset($query['component']) && isset($query['scope']);
            }))
            ->willReturn($response);

        $fetcher = new SourceFetcher($httpClient);
        $result = $fetcher->fetch(Pollutant::PM10);

        $this->assertSame('{"data":{}}', $result);
    }

    public function testFetchUsesProvidedDates(): void
    {
        $from = new \DateTimeImmutable('2024-06-15 10:00:00');
        $until = new \DateTimeImmutable('2024-06-15 12:00:00');

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{"data":{}}');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $this->anything(), $this->callback(function (array $options): bool {
                return $options['query']['date_from'] === '2024-06-15'
                    && $options['query']['time_from'] === 11
                    && $options['query']['date_to'] === '2024-06-15'
                    && $options['query']['time_to'] === 13;
            }))
            ->willReturn($response);

        $fetcher = new SourceFetcher($httpClient);
        $fetcher->fetch(Pollutant::PM10, $until, $from);
    }

    public function testFetchSendsCorrectPollutantParameters(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getContent')->willReturn('{}');

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->expects($this->once())
            ->method('request')
            ->with('GET', $this->anything(), $this->callback(function (array $options): bool {
                return $options['query']['component'] === 5
                    && $options['query']['scope'] === 2;
            }))
            ->willReturn($response);

        $fetcher = new SourceFetcher($httpClient);
        $fetcher->fetch(Pollutant::NO2, new \DateTimeImmutable(), new \DateTimeImmutable());
    }
}
