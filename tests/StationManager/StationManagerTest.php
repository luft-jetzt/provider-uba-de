<?php declare(strict_types=1);

namespace App\Tests\StationManager;

use App\StationCache\StationCacheInterface;
use App\StationManager\StationManager;
use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;

class StationManagerTest extends TestCase
{
    public function testStationExistsReturnsTrueForCachedStation(): void
    {
        $station = new Station();
        $station->setUbaStationId(123);

        $cache = $this->createMock(StationCacheInterface::class);
        $cache->method('getStationByUbaStationId')->with(123)->willReturn($station);

        $manager = new StationManager(
            $this->createMock(StationApiInterface::class),
            $cache,
        );

        $this->assertTrue($manager->stationExists(123));
    }

    public function testStationExistsReturnsFalseForUnknownStation(): void
    {
        $cache = $this->createMock(StationCacheInterface::class);
        $cache->method('getStationByUbaStationId')->willReturn(null);

        $manager = new StationManager(
            $this->createMock(StationApiInterface::class),
            $cache,
        );

        $this->assertFalse($manager->stationExists(999));
    }

    public function testGetStationByIdReturnsStation(): void
    {
        $station = new Station();
        $station->setUbaStationId(42);

        $cache = $this->createMock(StationCacheInterface::class);
        $cache->method('getStationByUbaStationId')->with(42)->willReturn($station);

        $manager = new StationManager(
            $this->createMock(StationApiInterface::class),
            $cache,
        );

        $this->assertSame($station, $manager->getStationById(42));
    }

    public function testCacheStationListCachesAllStations(): void
    {
        $station1 = new Station();
        $station2 = new Station();

        $cache = $this->createMock(StationCacheInterface::class);
        $cache->expects($this->exactly(2))
            ->method('addStation');

        $manager = new StationManager(
            $this->createMock(StationApiInterface::class),
            $cache,
        );

        $manager->cacheStationList([$station1, $station2]);
    }
}
