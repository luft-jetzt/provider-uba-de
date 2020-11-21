<?php declare(strict_types=1);

namespace App\Api;

use GuzzleHttp\Client;
use JMS\Serializer\SerializerInterface;

class StationApi implements StationApiInterface
{
    protected Client $client;
    protected SerializerInterface $serializer;

    public function __construct(SerializerInterface $serializer)
    {
        $this->client = new Client([
            'base_uri' => 'https://luft.jetzt/',
            'verify' => true,
        ]);

        $this->serializer = $serializer;
    }

    public function getStations(): array
    {
        $response = $this->client->get('/api/station');

        $stationList = $this->serializer->deserialize($response->getBody()->getContents(), 'array<App\Model\Station>', 'json');

        return $stationList;
    }
}