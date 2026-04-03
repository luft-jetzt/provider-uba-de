<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

readonly class Query
{
    public \DateTimeImmutable $fromDateTime;
    public \DateTimeImmutable $untilDateTime;

    public function __construct(
        public Pollutant $pollutant,
        ?\DateTimeImmutable $untilDateTime = null,
        ?\DateTimeImmutable $fromDateTime = null,
    ) {
        $this->untilDateTime = $untilDateTime ?? new \DateTimeImmutable();
        $this->fromDateTime = $fromDateTime ?? new \DateTimeImmutable();
    }
}
