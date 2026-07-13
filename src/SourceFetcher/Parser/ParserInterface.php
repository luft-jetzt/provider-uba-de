<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

interface ParserInterface
{
    /** @return list<\Caldera\LuftModel\Model\Value> */
    public function parse(string $responseString, string $pollutant): array;

    /**
     * Number of measurements dropped in the last parse() run because their
     * station was not found in the cache.
     */
    public function getSkippedStationValueCount(): int;
}
