<?php declare(strict_types=1);

namespace App\Provider\UmweltbundesamtDe\SourceFetcher\Reporting;

/**
 * Acht-Stunden-Tagesmaxima
 */
class Uba8SMWMAX extends AbstractReporting
{
    public function __construct(\DateTimeImmutable $endDateTime, \DateTimeImmutable $startDateTime = null)
    {
        $this->interval = new \DateInterval('PT8H');

        parent::__construct($endDateTime, $startDateTime);
    }
}
