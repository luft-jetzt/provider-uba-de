<?php declare(strict_types=1);

namespace App\SourceFetcher\QueryBuilder;

use App\SourceFetcher\Query\Query;

class QueryBuilder
{
    /**
     * The UBA JSON API expects and reports all timestamps in MEZ (CET, UTC+1,
     * without daylight saving time). Query times must therefore be converted to
     * this fixed offset before formatting, regardless of the server timezone.
     */
    private const UBA_TIMEZONE = '+01:00';

    protected function __construct()
    {
    }

    /** @return array<string, int|string> */
    public static function buildQueryParameters(Query $query): array
    {
        $timezone = new \DateTimeZone(self::UBA_TIMEZONE);
        $fromDateTime = $query->fromDateTime->setTimezone($timezone);
        $untilDateTime = $query->untilDateTime->setTimezone($timezone);

        return [
            'component' => $query->pollutant->component(),
            'scope' => $query->pollutant->scope(),
            'date_from' => $fromDateTime->format('Y-m-d'),
            'time_from' => ((int) $fromDateTime->format('H') + 1),
            'date_to' => $untilDateTime->format('Y-m-d'),
            'time_to' => ((int) $untilDateTime->format('H') + 1),
        ];
    }
}
