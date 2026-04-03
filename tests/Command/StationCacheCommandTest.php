<?php declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\StationCacheCommand;
use App\StationManager\StationManagerInterface;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class StationCacheCommandTest extends TestCase
{
    private function createCommandTester(?StationManagerInterface $manager = null): CommandTester
    {
        $manager ??= $this->createMock(StationManagerInterface::class);

        $command = new StationCacheCommand($manager);

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($application->find('station:cache'));
    }

    public function testExecuteLoadsAndCachesStations(): void
    {
        $station = new Station();
        $station->setStationCode('DEBW001');
        $station->setUbaStationId(1);

        $manager = $this->createMock(StationManagerInterface::class);
        $manager->expects($this->once())
            ->method('loadStationList')
            ->willReturn([$station]);
        $manager->expects($this->once())
            ->method('cacheStationList')
            ->with([$station]);

        $tester = $this->createCommandTester($manager);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Cached 1 stations from luft api', $tester->getDisplay());
    }

    public function testExecuteWithEmptyStationList(): void
    {
        $manager = $this->createMock(StationManagerInterface::class);
        $manager->method('loadStationList')->willReturn([]);

        $tester = $this->createCommandTester($manager);
        $tester->execute([]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Cached 0 stations', $tester->getDisplay());
    }
}
