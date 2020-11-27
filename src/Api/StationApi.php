<?php declare(strict_types=1);

namespace App\Api;

use App\Model\Station;
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
        $response = $this->client->get('/api/station?provider=uba_de');

        $stationList = $this->serializer->deserialize($response->getBody()->getContents(), 'array<App\Model\Station>', 'json');

        $assocStationList = [];

        /** @var Station $station */
        foreach ($stationList as $station) {
            $assocStationList[$station->getStationCode()] = $station;
        }

        return $assocStationList;
    }

    public function putStations(array $stationList): void
    {

    }

    public function postStations(array $stationList): void
    {

    }
}