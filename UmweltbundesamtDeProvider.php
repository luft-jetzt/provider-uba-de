<?php declare(strict_types=1);

namespace App\Provider\UmweltbundesamtDe;

use App\Provider\AbstractProvider;
use App\Provider\UmweltbundesamtDe\StationLoader\UmweltbundesamtStationLoader;

class UmweltbundesamtDeProvider extends AbstractProvider
{
    public function __construct(UmweltbundesamtStationLoader $umweltbundesamtStationLoader)
    {
        $this->stationLoader = $umweltbundesamtStationLoader;
    }

    public function getIdentifier(): string
    {
        return 'uba_de';
    }
}
