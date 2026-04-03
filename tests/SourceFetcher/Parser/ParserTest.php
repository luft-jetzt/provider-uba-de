<?php declare(strict_types=1);

namespace App\Tests\SourceFetcher\Parser;

use App\SourceFetcher\Parser\Parser;
use App\StationManager\StationManagerInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;

class ParserTest extends TestCase
{
    private function createParser(StationManagerInterface $stationManager): Parser
    {
        return new Parser($stationManager);
    }

    private function createStationManager(bool $exists = true, ?Station $station = null): StationManagerInterface
    {
        $manager = $this->createMock(StationManagerInterface::class);
        $manager->method('stationExists')->willReturn($exists);
        $manager->method('getStationById')->willReturn($station);
        return $manager;
    }

    private function createStation(string $stationCode = 'DEBW001'): Station
    {
        $station = new Station();
        $station->setStationCode($stationCode);
        return $station;
    }

    public function testParseValidResponse(): void
    {
        $station = $this->createStation();
        $parser = $this->createParser($this->createStationManager(true, $station));

        $response = json_encode([
            'data' => [
                123 => [
                    [123, 1, 42.5, '2024-06-15 12:00:00'],
                ],
            ],
        ]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(1, $result);
        $this->assertSame('DEBW001', $result[0]->getStationCode());
        $this->assertSame('pm10', $result[0]->getPollutant());
        $this->assertSame(42.5, $result[0]->getValue());
    }

    public function testParseSkipsZeroValues(): void
    {
        $station = $this->createStation();
        $parser = $this->createParser($this->createStationManager(true, $station));

        $response = json_encode([
            'data' => [
                123 => [
                    [123, 1, 0, '2024-06-15 12:00:00'],
                ],
            ],
        ]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(0, $result);
    }

    public function testParseSkipsNegativeValues(): void
    {
        $station = $this->createStation();
        $parser = $this->createParser($this->createStationManager(true, $station));

        $response = json_encode([
            'data' => [
                123 => [
                    [123, 1, -1.0, '2024-06-15 12:00:00'],
                ],
            ],
        ]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(0, $result);
    }

    public function testParseSkipsUnknownStations(): void
    {
        $parser = $this->createParser($this->createStationManager(false));

        $response = json_encode([
            'data' => [
                999 => [
                    [999, 1, 42.5, '2024-06-15 12:00:00'],
                ],
            ],
        ]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(0, $result);
    }

    public function testParseEmptyDataSet(): void
    {
        $parser = $this->createParser($this->createStationManager());

        $response = json_encode(['data' => []]);

        $result = $parser->parse($response, 'pm10');

        $this->assertCount(0, $result);
    }
}
