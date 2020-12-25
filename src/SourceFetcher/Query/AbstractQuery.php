<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

use Carbon\Carbon;

abstract class AbstractQuery implements QueryInterface
{
    protected int $component;

    protected int $scope;

    protected Carbon $fromDateTime;

    protected Carbon $untilDateTime;

    public function __construct(Carbon $untilDateTime = null, Carbon $fromDateTime = null)
    {
        $this->fromDateTime = $fromDateTime ?? new Carbon();
        $this->untilDateTime = $untilDateTime ?? new Carbon();
    }

    public function getComponent(): int
    {
        return $this->component;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public function getFromDateTime(): Carbon
    {
        return $this->fromDateTime;
    }

    public function setFromDateTime(Carbon $fromDateTime): QueryInterface
    {
        $this->fromDateTime = $fromDateTime;

        return $this;
    }

    public function getUntilDateTime(): Carbon
    {
        return $this->untilDateTime;
    }

    public function setUntilDateTime(Carbon $untilDateTime): QueryInterface
    {
        $this->untilDateTime = $untilDateTime;

        return $this;
    }
}
