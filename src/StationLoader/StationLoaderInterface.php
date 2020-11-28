<?php declare(strict_types=1);

namespace App\StationLoader;

interface StationLoaderInterface
{
    public function load(): StationLoadResult;
    public function setUpdate(bool $update = false): StationLoaderInterface;
    public function getExistingStationList(): array;
}
