<?php declare(strict_types=1);

namespace App\SourceFetcher\QueryBuilder;

use App\SourceFetcher\Query\QueryInterface;

class QueryBuilder
{
    protected function __construct()
    {

    }

    public static function buildQueryString(QueryInterface $query): string
    {
        $data = [
            'component' => $query->getComponent(),
            'scope' => $query->getScope(),
            'date_from' => $query->getFromDateTime()->format('Y-m-d'),
            'time_from' => ((int) $query->getFromDateTime()->format('H') + 1),
            'date_to' => $query->getUntilDateTime()->format('Y-m-d'),
            'time_to' => ((int) $query->getUntilDateTime()->format('H') + 1),
        ];

        return http_build_query($data);
    }
}
