<?php declare(strict_types=1);

namespace App\Tests\StationLoader;

use App\StationLoader\StationLoader;
use PHPUnit\Framework\TestCase;

class StationLoaderTest extends TestCase
{
    /**
     * Test mapAreaType via reflection since it's protected.
     */
    public function testMapAreaType(): void
    {
        $loader = $this->createPartialLoader();
        $method = new \ReflectionMethod(StationLoader::class, 'mapAreaType');

        $this->assertSame('suburban', $method->invoke($loader, 'vorstädtisch'));
        $this->assertSame('urban', $method->invoke($loader, 'städtisch'));
        $this->assertSame('rural', $method->invoke($loader, 'ländlich'));
    }

    public function testMapAreaTypeUnknownThrows(): void
    {
        $loader = $this->createPartialLoader();
        $method = new \ReflectionMethod(StationLoader::class, 'mapAreaType');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown area type: unknown');
        $method->invoke($loader, 'unknown');
    }

    public function testMapStationType(): void
    {
        $loader = $this->createPartialLoader();
        $method = new \ReflectionMethod(StationLoader::class, 'mapStationType');

        $this->assertSame('background', $method->invoke($loader, 'Hintergrund'));
        $this->assertSame('traffic', $method->invoke($loader, 'Verkehr'));
        $this->assertSame('industrial', $method->invoke($loader, 'Industrie'));
    }

    public function testMapStationTypeUnknownThrows(): void
    {
        $loader = $this->createPartialLoader();
        $method = new \ReflectionMethod(StationLoader::class, 'mapStationType');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown station type: unknown');
        $method->invoke($loader, 'unknown');
    }

    private function createPartialLoader(): StationLoader
    {
        $stationApi = $this->createMock(\Caldera\LuftApiBundle\Api\StationApiInterface::class);
        $httpClient = $this->createMock(\Symfony\Contracts\HttpClient\HttpClientInterface::class);

        return new StationLoader($stationApi, $httpClient);
    }
}
