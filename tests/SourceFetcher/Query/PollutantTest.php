<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher\Query;

use App\SourceFetcher\Query\Pollutant;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PollutantTest extends TestCase
{
    #[DataProvider('componentProvider')]
    public function testComponent(Pollutant $pollutant, int $expectedComponent): void
    {
        $this->assertSame($expectedComponent, $pollutant->component());
    }

    /** @return array<string, array{Pollutant, int}> */
    public static function componentProvider(): array
    {
        return [
            'PM10' => [Pollutant::PM10, 1],
            'CO' => [Pollutant::CO, 2],
            'O3' => [Pollutant::O3, 3],
            'SO2' => [Pollutant::SO2, 4],
            'NO2' => [Pollutant::NO2, 5],
        ];
    }

    #[DataProvider('scopeProvider')]
    public function testScope(Pollutant $pollutant, int $expectedScope): void
    {
        $this->assertSame($expectedScope, $pollutant->scope());
    }

    /** @return array<string, array{Pollutant, int}> */
    public static function scopeProvider(): array
    {
        return [
            'PM10' => [Pollutant::PM10, 2],
            'NO2' => [Pollutant::NO2, 2],
            'O3' => [Pollutant::O3, 2],
            'SO2' => [Pollutant::SO2, 2],
            'CO' => [Pollutant::CO, 4],
        ];
    }

    public function testFromString(): void
    {
        $this->assertSame(Pollutant::PM10, Pollutant::from('pm10'));
        $this->assertSame(Pollutant::NO2, Pollutant::from('no2'));
    }

    public function testTryFromInvalidReturnsNull(): void
    {
        $this->assertNull(Pollutant::tryFrom('invalid'));
    }
}
