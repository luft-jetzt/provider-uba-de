<?php declare(strict_types=1);

namespace App\Tests\StationCache;

use App\StationCache\StationCache;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class StationCacheTest extends TestCase
{
    private string $cacheDir;
    private StationCache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/luft_test_cache_' . uniqid();
        mkdir($this->cacheDir, 0777, true);

        // StationCache uses a hardcoded cache directory from the interface constant.
        // We test it as-is — the real filesystem adapter is used.
        $this->cache = new StationCache();
    }

    protected function tearDown(): void
    {
        (new Filesystem())->remove($this->cacheDir);
    }

    public function testAddAndRetrieveStation(): void
    {
        $station = new Station();
        $station->setUbaStationId(123);
        $station->setStationCode('DEBW001');

        $this->cache->addStation($station);

        $retrieved = $this->cache->getStationByUbaStationId(123);

        $this->assertNotNull($retrieved);
        $this->assertSame('DEBW001', $retrieved->getStationCode());
        $this->assertSame(123, $retrieved->getUbaStationId());
    }

    public function testGetNonExistentStationReturnsNull(): void
    {
        $result = $this->cache->getStationByUbaStationId(999999);

        $this->assertNull($result);
    }

    public function testAddStationReturnsSelf(): void
    {
        $station = new Station();
        $station->setUbaStationId(1);

        $result = $this->cache->addStation($station);

        $this->assertSame($this->cache, $result);
    }

    public function testOverwriteExistingStation(): void
    {
        $station1 = new Station();
        $station1->setUbaStationId(42);
        $station1->setStationCode('OLD');
        $this->cache->addStation($station1);

        $station2 = new Station();
        $station2->setUbaStationId(42);
        $station2->setStationCode('NEW');
        $this->cache->addStation($station2);

        $retrieved = $this->cache->getStationByUbaStationId(42);
        $this->assertSame('NEW', $retrieved->getStationCode());
    }
}
