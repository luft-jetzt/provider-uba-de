<?php declare(strict_types=1);

namespace App\SourceFetcher;

use App\SourceFetcher\Query\QueryInterface;
use Curl\Curl;

class UbSourceFetcher implements SourceFetcherInterface
{
    public function query(QueryInterface $query = null): string
    {
        $curl = new Curl();

        $queryString = 'https://www.umweltbundesamt.de/uaq/csv/stations/data?' . $query->getQueryString();

        $curl->get($queryString);

        return $curl->response;
    }
}
