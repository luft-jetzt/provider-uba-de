<?php declare(strict_types=1);

namespace App\SourceFetcher\Query;

class NO2Query extends AbstractQuery
{
    protected int $component = 5;

    protected array $scope = [2, 3];
}
