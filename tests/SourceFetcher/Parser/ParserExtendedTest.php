<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher\Parser;

use App\SourceFetcher\Parser\Parser;
use App\StationManager\StationManagerInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;

class ParserExtendedTest extends TestCase
{
    private function createStation(string $code): Station
    {
        $station = new Station();
        $station->setStationCode($code);
        return $station;
    }

    private function createManager(array $stations): StationManagerInterface
    {
        $manager = $this->createMock(StationManagerInterface::class);
        $manager->method('stationExists')->willReturnCallback(
            fn (int $id) => isset($stations[$id])
        );
        $manager->method('getStationById')->willReturnCallback(
            fn (int $id) => $stations[$id] ?? null
        );
        return $manager;
    }

    public function testParseMultipleStations(): void
    {
        $stations = [
            100 => $this->createStation('DEBW001'),
            200 => $this->createStation('DEBW002'),
        ];
        $parser = new Parser($this->createManager($stations));

        $response = json_encode([
            'data' => [
                100 => [[100, 1, 25.0, '2024-06-15 12:00:00']],
                200 => [[200, 1, 30.0, '2024-06-15 12:00:00']],
            ],
        ]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(2, $result);
        $codes = array_map(fn ($v) => $v->getStationCode(), $result);
        $this->assertContains('DEBW001', $codes);
        $this->assertContains('DEBW002', $codes);
    }

    public function testParseMultipleMeasurementsPerStation(): void
    {
        $stations = [100 => $this->createStation('DEBW001')];
        $parser = new Parser($this->createManager($stations));

        $response = json_encode([
            'data' => [
                100 => [
                    [100, 1, 20.0, '2024-06-15 10:00:00'],
                    [100, 1, 25.0, '2024-06-15 11:00:00'],
                    [100, 1, 30.0, '2024-06-15 12:00:00'],
                ],
            ],
        ]);

        $result = $parser->parse($response, 'no2');

        $this->assertCount(3, $result);
        foreach ($result as $value) {
            $this->assertSame('DEBW001', $value->getStationCode());
            $this->assertSame('no2', $value->getPollutant());
        }
    }

    public function testParseSetsCorrectDateTime(): void
    {
        $stations = [100 => $this->createStation('DEBW001')];
        $parser = new Parser($this->createManager($stations));

        $response = json_encode([
            'data' => [
                100 => [[100, 1, 42.0, '2024-12-25 18:30:00']],
            ],
        ]);

        $result = $parser->parse($response, 'o3');

        $this->assertSame('2024-12-25 18:30:00', $result[0]->getDateTime()->format('Y-m-d H:i:s'));
    }

    public function testParseMixesValidAndInvalidValues(): void
    {
        $stations = [
            100 => $this->createStation('DEBW001'),
            200 => $this->createStation('DEBW002'),
        ];
        $parser = new Parser($this->createManager($stations));

        $response = json_encode([
            'data' => [
                100 => [
                    [100, 1, 50.0, '2024-06-15 12:00:00'],  // valid
                    [100, 1, 0, '2024-06-15 13:00:00'],      // zero → skip
                    [100, 1, -5.0, '2024-06-15 14:00:00'],   // negative → skip
                ],
                200 => [
                    [200, 1, 10.0, '2024-06-15 12:00:00'],   // valid
                ],
                300 => [
                    [300, 1, 99.0, '2024-06-15 12:00:00'],   // unknown station → skip
                ],
            ],
        ]);

        $result = $parser->parse($response, 'so2');

        $this->assertCount(2, $result);
    }

    public function testParseSetsValueCorrectly(): void
    {
        $stations = [100 => $this->createStation('DEBW001')];
        $parser = new Parser($this->createManager($stations));

        $response = json_encode([
            'data' => [
                100 => [[100, 1, 123.456, '2024-06-15 12:00:00']],
            ],
        ]);

        $result = $parser->parse($response, 'co');

        $this->assertEqualsWithDelta(123.456, $result[0]->getValue(), 0.001);
    }
}
