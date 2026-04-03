<?php declare(strict_types=1);

namespace App\Tests\StationLoader;

use App\StationLoader\StationLoader;
use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class StationLoaderLoadTest extends TestCase
{
    private function createStationData(
        int $id = 1,
        string $code = 'DEBW001',
        string $title = 'Stuttgart Mitte',
        string $city = 'Stuttgart',
        string $startDate = '2020-01-01',
        float $longitude = 9.18,
        float $latitude = 48.78,
        string $areaType = 'städtisch',
        string $stationType = 'Hintergrund',
    ): array {
        return [
            0 => $id,
            1 => $code,
            2 => $title,
            3 => $city,
            4 => null,
            5 => $startDate,
            6 => null,
            7 => $longitude,
            8 => $latitude,
            9 => null,
            10 => null,
            11 => null,
            12 => '08',
            13 => 'Baden-Württemberg',
            14 => null,
            15 => $areaType,
            16 => $stationType,
        ];
    }

    private function createLoader(array $apiStations, array $ubaStations): StationLoader
    {
        $stationApi = $this->createMock(StationApiInterface::class);
        $stationApi->method('getStations')->willReturn($apiStations);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('toArray')->willReturn(['stations' => $ubaStations]);

        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('request')->willReturn($response);

        return new StationLoader($stationApi, $httpClient);
    }

    public function testLoadDetectsNewStations(): void
    {
        $loader = $this->createLoader([], [
            $this->createStationData(id: 1, code: 'DEBW001'),
            $this->createStationData(id: 2, code: 'DEBW002'),
        ]);

        $result = $loader->load();

        $this->assertCount(0, $result->getExistingStationList());
        $this->assertCount(2, $result->getNewStationList());
        $this->assertArrayHasKey('DEBW001', $result->getNewStationList());
        $this->assertArrayHasKey('DEBW002', $result->getNewStationList());
    }

    public function testLoadRecognizesExistingStations(): void
    {
        $existingStation = new Station();
        $existingStation->setStationCode('DEBW001');

        $loader = $this->createLoader(
            ['DEBW001' => $existingStation],
            [$this->createStationData(id: 1, code: 'DEBW001')],
        );

        $result = $loader->load();

        $this->assertCount(1, $result->getExistingStationList());
        $this->assertCount(0, $result->getNewStationList());
        $this->assertCount(0, $result->getChangedStationList());
    }

    public function testLoadWithUpdateMarksChangedStations(): void
    {
        $existingStation = new Station();
        $existingStation->setStationCode('DEBW001');

        $loader = $this->createLoader(
            ['DEBW001' => $existingStation],
            [$this->createStationData(id: 1, code: 'DEBW001', title: 'Updated Title')],
        );
        $loader->setUpdate(true);

        $result = $loader->load();

        $this->assertCount(1, $result->getChangedStationList());
        $this->assertSame('Updated Title', $result->getChangedStationList()['DEBW001']->getTitle());
    }

    public function testLoadSkipsStationsWithoutCode(): void
    {
        $stationData = $this->createStationData();
        $stationData[1] = null; // no station code

        $loader = $this->createLoader([], [$stationData]);

        $result = $loader->load();

        $this->assertCount(0, $result->getNewStationList());
    }

    public function testLoadSkipsStationsWithEmptyCode(): void
    {
        $stationData = $this->createStationData();
        $stationData[1] = ''; // empty station code

        $loader = $this->createLoader([], [$stationData]);

        $result = $loader->load();

        $this->assertCount(0, $result->getNewStationList());
    }

    public function testLoadSkipsStationsWithMissingCodeField(): void
    {
        $stationData = [0 => 1]; // no field index 1

        $loader = $this->createLoader([], [$stationData]);

        $result = $loader->load();

        $this->assertCount(0, $result->getNewStationList());
    }

    public function testCreateStationSetsAllFields(): void
    {
        $loader = $this->createLoader([], [
            $this->createStationData(
                id: 42,
                code: 'DEBY001',
                title: 'München Nord',
                startDate: '2019-05-15',
                longitude: 11.57,
                latitude: 48.14,
                areaType: 'vorstädtisch',
                stationType: 'Verkehr',
            ),
        ]);

        $result = $loader->load();

        $station = $result->getNewStationList()['DEBY001'];
        $this->assertSame('DEBY001', $station->getStationCode());
        $this->assertSame('München Nord', $station->getTitle());
        $this->assertSame('uba_de', $station->getProvider());
        $this->assertSame(42, $station->getUbaStationId());
        $this->assertEqualsWithDelta(48.14, $station->getLatitude(), 0.001);
        $this->assertEqualsWithDelta(11.57, $station->getLongitude(), 0.001);
        $this->assertSame('suburban', $station->getAreaType());
        $this->assertSame('traffic', $station->getStationType());
    }

    public function testSetUpdateReturnsSelf(): void
    {
        $loader = $this->createLoader([], []);
        $this->assertSame($loader, $loader->setUpdate(true));
    }

    public function testLoadWithoutUpdateDoesNotMarkChanged(): void
    {
        $existingStation = new Station();
        $existingStation->setStationCode('DEBW001');

        $loader = $this->createLoader(
            ['DEBW001' => $existingStation],
            [$this->createStationData(id: 1, code: 'DEBW001')],
        );
        // update is false by default

        $result = $loader->load();

        $this->assertCount(0, $result->getChangedStationList());
    }
}
