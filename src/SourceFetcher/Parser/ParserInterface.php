<?php declare(strict_types=1);

namespace App\SourceFetcher\Parser;

interface ParserInterface
{
    public function parse(string $responseString, int $pollutant): array;
}
