<?php declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\StationLoadCommand;
use App\StationLoader\StationLoadResult;
use App\StationLoader\StationLoaderInterface;
use Caldera\LuftApiBundle\Api\StationApiInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StationLoadCommandTest extends TestCase
{
    private function createCommandTester(
        ?StationLoaderInterface $loader = null,
        ?StationApiInterface $stationApi = null,
    ): CommandTester {
        $loader ??= $this->createMock(StationLoaderInterface::class);
        $stationApi ??= $this->createMock(StationApiInterface::class);

        $command = new StationLoadCommand($loader, $stationApi);

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($application->find('station:load'));
    }

    public function testExecutePutsNewStations(): void
    {
        $newStation = new Station();
        $newStation->setStationCode('DEBW001');

        $result = new StationLoadResult();
        $result->addNewStation($newStation);

        $loader = $this->createMock(StationLoaderInterface::class);
        $loader->method('load')->willReturn($result);

        $stationApi = $this->createMock(StationApiInterface::class);
        $stationApi->expects($this->once())
            ->method('putStations')
            ->with($this->callback(fn (array $stations) => count($stations) === 1));
        $stationApi->expects($this->once())
            ->method('postStations')
            ->with([]);

        $tester = $this->createCommandTester($loader, $stationApi);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('1 new stations', $tester->getDisplay());
    }

    public function testExecutePostsChangedStations(): void
    {
        $changedStation = new Station();
        $changedStation->setStationCode('DEBW002');

        $result = new StationLoadResult();
        $result->addChangedStation($changedStation);

        $loader = $this->createMock(StationLoaderInterface::class);
        $loader->method('load')->willReturn($result);

        $stationApi = $this->createMock(StationApiInterface::class);
        $stationApi->expects($this->once())
            ->method('postStations')
            ->with($this->callback(fn (array $stations) => count($stations) === 1));

        $tester = $this->createCommandTester($loader, $stationApi);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('1 changed stations', $tester->getDisplay());
    }

    public function testExecuteWithNoChanges(): void
    {
        $existing = new Station();
        $existing->setStationCode('DEBW001');

        $result = new StationLoadResult();
        $result->setExistingStationList(['DEBW001' => $existing]);

        $loader = $this->createMock(StationLoaderInterface::class);
        $loader->method('load')->willReturn($result);

        $tester = $this->createCommandTester($loader);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('1 existing stations', $tester->getDisplay());
        $this->assertStringContainsString('0 new stations', $tester->getDisplay());
    }
}
