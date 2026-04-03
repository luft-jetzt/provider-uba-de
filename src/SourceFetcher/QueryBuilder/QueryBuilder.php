<?php declare(strict_types=1);

namespace App\SourceFetcher\QueryBuilder;

use App\SourceFetcher\Query\QueryInterface;

class QueryBuilder
{
    protected function __construct()
    {
    }

    public static function buildQueryParameters(QueryInterface $query): array
    {
        return [
            'component' => $query->getComponent(),
            'scope' => $query->getScope(),
            'date_from' => $query->getFromDateTime()->format('Y-m-d'),
            'time_from' => ((int) $query->getFromDateTime()->format('H') + 1),
            'date_to' => $query->getUntilDateTime()->format('Y-m-d'),
            'time_to' => ((int) $query->getUntilDateTime()->format('H') + 1),
        ];
    }
}
