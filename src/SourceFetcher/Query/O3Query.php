<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

class O3Query extends AbstractQuery
{
    protected int $component = 3;

    protected array $scope = [2, 3, 4, 5];
}
