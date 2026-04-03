<?php declare(strict_types=1);

namespace App\SourceFetcher;

use App\SourceFetcher\Query\Pollutant;

interface SourceFetcherInterface
{
    public function fetch(Pollutant $pollutant, ?\DateTimeImmutable $untilDateTime = null, ?\DateTimeImmutable $fromDateTime = null): string;
}
