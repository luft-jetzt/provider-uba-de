<?php declare(strict_types=1);

namespace App\Model;

use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("ALL")
 */
class Station
{
    /**
     * @JMS\Expose()
     */
    protected string $stationCode;

    /**
     * @JMS\Expose()
     */
    protected int $ubaStationId;

    public function getStationCode(): string
    {
        return $this->stationCode;
    }

    public function setStationCode(string $stationCode): Station
    {
        $this->stationCode = $stationCode;

        return $this;
    }

    public function getUbaStationId(): int
    {
        return $this->ubaStationId;
    }

    public function setUbaStationId(int $ubaStationId): Station
    {
        $this->ubaStationId = $ubaStationId;

        return $this;
    }
}
