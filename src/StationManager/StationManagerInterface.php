<?php declare(strict_types=1);

namespace App\StationManager;

use Caldera\LuftApiBundle\Model\Station;

interface StationManagerInterface
{
    public function loadStationList(): array;
    public function stationExists(int $ubaStationId): bool;
    public function getStationById(int $ubaStationId): ?Station;
}
