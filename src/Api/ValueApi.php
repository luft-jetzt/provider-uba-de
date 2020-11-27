<?php declare(strict_types=1);

namespace App\Api;


use App\Model\Value;

class ValueApi extends AbstractApi implements ValueApiInterface
{
    public function putValue(Value $value): void
    {
        $this->client->put('/api/value', [
            'body' => $this->serializer->serialize($value, 'json'),
        ]);
    }

    public function putValues(array $valueList): void
    {
        $this->client->put('/api/value', [
            'body' => $this->serializer->serialize($valueList, 'json'),
        ]);
    }
}