<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

use Carbon\Carbon;

abstract class AbstractQuery implements QueryInterface
{
    protected int $component;

    protected array $scope = [];

    protected Carbon $fromDateTime;

    protected Carbon $untilDateTime;

    public function __construct()
    {
        $this->fromDateTime = new Carbon();
        $this->untilDateTime = new Carbon();
    }

    public function getComponent(): int
    {
        return $this->component;
    }

    public function getScope(): array
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
