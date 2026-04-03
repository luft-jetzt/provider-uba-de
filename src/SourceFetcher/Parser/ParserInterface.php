<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

interface ParserInterface
{
    /** @return list<\Caldera\LuftModel\Model\Value> */
    public function parse(string $responseString, string $pollutant): array;
}
