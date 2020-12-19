<?php declare(strict_types=1);

namespace App\Api;

use App\Model\Station;

class StationApi extends AbstractApi implements StationApiInterface
{
    public function getStations(): array
    {
        $response = $this->client->get('/api/station?provider=uba_de');

        $stationList = $this->serializer->deserialize($response->getBody()->getContents(), 'array<App\Model\Station>', self::SERIALIZER_FORMAT);

        $assocStationList = [];

        /** @var Station $station */
        foreach ($stationList as $station) {
            $assocStationList[$station->getStationCode()] = $station;
        }

        return $assocStationList;
    }

    public function putStations(array $stationList): void
    {
        $this->client->put('/api/station', [
            'body' => $this->serializer->serialize($stationList, self::SERIALIZER_FORMAT),
        ]);
    }

    public function postStations(array $stationList): void
    {
        /** @var Station $station */
        foreach ($stationList as $station) {
            $postApiUrl = sprintf('/api/station/%d', $station->getStationCode());

            $this->client->post($postApiUrl, [
                'body' => $this->serializer->serialize($station, self::SERIALIZER_FORMAT),
            ]);
        }
    }
}