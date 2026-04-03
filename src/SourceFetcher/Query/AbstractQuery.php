<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

abstract class AbstractQuery implements QueryInterface
{
    protected int $component;

    protected int $scope;

    protected \DateTimeImmutable $fromDateTime;

    protected \DateTimeImmutable $untilDateTime;

    public function __construct(?\DateTimeImmutable $untilDateTime = null, ?\DateTimeImmutable $fromDateTime = null)
    {
        $this->fromDateTime = $fromDateTime ?? new \DateTimeImmutable();
        $this->untilDateTime = $untilDateTime ?? new \DateTimeImmutable();
    }

    public function getComponent(): int
    {
        return $this->component;
    }

    public function getScope(): int
    {
        return $this->scope;
    }

    public function getFromDateTime(): \DateTimeImmutable
    {
        return $this->fromDateTime;
    }

    public function setFromDateTime(\DateTimeImmutable $fromDateTime): QueryInterface
    {
        $this->fromDateTime = $fromDateTime;

        return $this;
    }

    public function getUntilDateTime(): \DateTimeImmutable
    {
        return $this->untilDateTime;
    }

    public function setUntilDateTime(\DateTimeImmutable $untilDateTime): QueryInterface
    {
        $this->untilDateTime = $untilDateTime;

        return $this;
    }
}
