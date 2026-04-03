<?php declare(strict_types=1);

namespace App\Tests\StationLoader;

use App\StationLoader\StationLoadResult;
use Caldera\LuftModel\Model\Station;
use PHPUnit\Framework\TestCase;

class StationLoadResultTest extends TestCase
{
    public function testAddNewStation(): void
    {
        $result = new StationLoadResult();
        $station = new Station();
        $station->setStationCode('DEBW001');

        $result->addNewStation($station);

        $this->assertCount(1, $result->getNewStationList());
        $this->assertSame($station, $result->getNewStationList()['DEBW001']);
    }

    public function testAddChangedStation(): void
    {
        $result = new StationLoadResult();
        $station = new Station();
        $station->setStationCode('DEBW002');

        $result->addChangedStation($station);

        $this->assertCount(1, $result->getChangedStationList());
        $this->assertSame($station, $result->getChangedStationList()['DEBW002']);
    }

    public function testSetExistingStationList(): void
    {
        $result = new StationLoadResult();
        $station = new Station();
        $station->setStationCode('DEBW003');

        $result->setExistingStationList(['DEBW003' => $station]);

        $this->assertCount(1, $result->getExistingStationList());
        $this->assertSame($station, $result->getExistingStationList()['DEBW003']);
    }

    public function testStationCodeAsArrayKey(): void
    {
        $result = new StationLoadResult();

        $station1 = new Station();
        $station1->setStationCode('DEBW001');
        $result->addNewStation($station1);

        $station2 = new Station();
        $station2->setStationCode('DEBW002');
        $result->addNewStation($station2);

        $this->assertArrayHasKey('DEBW001', $result->getNewStationList());
        $this->assertArrayHasKey('DEBW002', $result->getNewStationList());
    }
}
