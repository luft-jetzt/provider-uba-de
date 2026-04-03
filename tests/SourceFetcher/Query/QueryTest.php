<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher\Query;

use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\Query\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    public function testDefaultDateTimesAreSet(): void
    {
        $before = new \DateTimeImmutable();
        $query = new Query(Pollutant::PM10);
        $after = new \DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before, $query->fromDateTime);
        $this->assertLessThanOrEqual($after, $query->fromDateTime);
        $this->assertGreaterThanOrEqual($before, $query->untilDateTime);
        $this->assertLessThanOrEqual($after, $query->untilDateTime);
    }

    public function testCustomDateTimesAreUsed(): void
    {
        $from = new \DateTimeImmutable('2024-01-01 10:00:00');
        $until = new \DateTimeImmutable('2024-01-01 12:00:00');

        $query = new Query(Pollutant::NO2, $until, $from);

        $this->assertSame($from, $query->fromDateTime);
        $this->assertSame($until, $query->untilDateTime);
    }

    public function testPollutantIsAccessible(): void
    {
        $query = new Query(Pollutant::O3);
        $this->assertSame(Pollutant::O3, $query->pollutant);
    }
}
