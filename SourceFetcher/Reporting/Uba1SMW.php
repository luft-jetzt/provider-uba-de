<?php declare(strict_types=1);

namespace App\Provider\UmweltbundesamtDe\SourceFetcher\Reporting;

/**
 * Ein-Stunden-Mittelwert
 */
class Uba1SMW extends AbstractReporting
{
    public function __construct(\DateTimeImmutable $dateTime)
    {
        $this->interval = new \DateInterval('PT1H');

        parent::__construct($dateTime);
    }
}
