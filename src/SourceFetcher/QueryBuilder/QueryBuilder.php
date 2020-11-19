<?php declare(strict_types=1);

namespace App\Provider\UmweltbundesamtDe\SourceFetcher\QueryBuilder;

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
            'time_from' => $query->getFromDateTime()->format('H'),
            'date_to' => $query->getUntilDateTime()->format('Y-m-d'),
            'time_to' => $query->getUntilDateTime()->format('H'),
        ];

        return http_build_query($data);
    }
}