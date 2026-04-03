<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

interface QueryInterface
{
    public function getComponent(): int;

    public function getScope(): int;

    public function getFromDateTime(): \DateTimeImmutable;

    public function setFromDateTime(\DateTimeImmutable $fromDateTime): QueryInterface;

    public function getUntilDateTime(): \DateTimeImmutable;

    public function setUntilDateTime(\DateTimeImmutable $untilDateTime): QueryInterface;
}
