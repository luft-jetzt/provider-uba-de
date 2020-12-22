<?php declare(strict_types=1);

namespace App\SourceFetcher;

use Carbon\Carbon;

interface SourceFetcherInterface
{
    public function fetch(string $pollutantIdentifier, Carbon $untilDateTime = null, Carbon $fromDateTime = null): string;
}
