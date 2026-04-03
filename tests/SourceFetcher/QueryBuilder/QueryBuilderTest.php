<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher\QueryBuilder;

use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\Query\Query;
use App\SourceFetcher\QueryBuilder\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    public function testBuildQueryParametersWithValidQuery(): void
    {
        $from = new \DateTimeImmutable('2024-06-15 10:00:00');
        $until = new \DateTimeImmutable('2024-06-15 12:00:00');
        $query = new Query(Pollutant::PM10, $until, $from);

        $params = QueryBuilder::buildQueryParameters($query);

        $this->assertSame(1, $params['component']);
        $this->assertSame(2, $params['scope']);
        $this->assertSame('2024-06-15', $params['date_from']);
        $this->assertSame(11, $params['time_from']);
        $this->assertSame('2024-06-15', $params['date_to']);
        $this->assertSame(13, $params['time_to']);
    }

    public function testTimeOffsetByOne(): void
    {
        $from = new \DateTimeImmutable('2024-06-15 00:00:00');
        $until = new \DateTimeImmutable('2024-06-15 23:00:00');
        $query = new Query(Pollutant::NO2, $until, $from);

        $params = QueryBuilder::buildQueryParameters($query);

        $this->assertSame(1, $params['time_from']);
        $this->assertSame(24, $params['time_to']);
    }

    public function testCOQueryUsesScope4(): void
    {
        $query = new Query(Pollutant::CO);

        $params = QueryBuilder::buildQueryParameters($query);

        $this->assertSame(2, $params['component']);
        $this->assertSame(4, $params['scope']);
    }
}
