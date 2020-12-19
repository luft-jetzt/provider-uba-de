<?php declare(strict_types=1);

namespace App\StationLoader;

use Caldera\LuftApiBundle\Model\Station;

class StationLoadResult
{
    protected array $existingStationList = [];
    protected array $newStationList = [];
    protected array $changedStationList = [];

    public function setExistingStationList(array $existingStationList): self
    {
        $this->existingStationList = $existingStationList;

        return $this;
    }

    public function getExistingStationList(): array
    {
        return $this->existingStationList;
    }

    public function addNewStation(Station $station): self
    {
        $this->newStationList[$station->getStationCode()] = $station;

        return $this;
    }

    public function getNewStationList(): array
    {
        return $this->newStationList;
    }

    public function addChangedStation(Station $station): self
    {
        $this->changedStationList[$station->getStationCode()] = $station;

        return $this;
    }

    public function getChangedStationList(): array
    {
        return $this->changedStationList;
    }
}
