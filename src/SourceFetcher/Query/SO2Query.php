<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

class SO2Query extends AbstractQuery
{
    protected int $component = 4;

    protected array $scope = [1, 2, 3];
}
