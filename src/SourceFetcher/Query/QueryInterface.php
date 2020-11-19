<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

use Carbon\Carbon;

interface QueryInterface
{
    public function getComponent(): int;

    public function getScope(): array;

    public function getFromDateTime(): Carbon;

    public function setFromDateTime(Carbon $fromDateTime): QueryInterface;

    public function getUntilDateTime(): Carbon;

    public function setUntilDateTime(Carbon $untilDateTime): QueryInterface;
}
