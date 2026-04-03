<?php declare(strict_types=1);

namespace App\SourceFetcher\QueryBuilder;

use App\SourceFetcher\Query\Query;

class QueryBuilder
{
    protected function __construct()
    {
    }

    /** @return array<string, int|string> */
    public static function buildQueryParameters(Query $query): array
    {
        return [
            'component' => $query->pollutant->component(),
            'scope' => $query->pollutant->scope(),
            'date_from' => $query->fromDateTime->format('Y-m-d'),
            'time_from' => ((int) $query->fromDateTime->format('H') + 1),
            'date_to' => $query->untilDateTime->format('Y-m-d'),
            'time_to' => ((int) $query->untilDateTime->format('H') + 1),
        ];
    }
}
