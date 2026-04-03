<?php declare(strict_types=1);

namespace App\SourceFetcher;

interface SourceFetcherInterface
{
    public function fetch(string $pollutantIdentifier, ?\DateTimeImmutable $untilDateTime = null, ?\DateTimeImmutable $fromDateTime = null): string;
}
