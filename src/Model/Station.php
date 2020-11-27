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
    protected ?string $stationCode = null;

    /**
     * @JMS\Expose()
     */
    protected ?int $ubaStationId = null;

    /**
     * @JMS\Expose()
     */
    protected string $title;

    /**
     * @JMS\Expose()
     */
    protected float $latitude;

    /**
     * @JMS\Expose()
     */
    protected float $longitude;

    /**
     * @JMS\Expose()
     */
    protected string $cityName;

    /**
     * @JMS\Expose()
     * @JMS\Type("DateTime<'U'>")
     */
    protected \DateTime $fromDate;

    /**
     * @JMS\Expose()
     * @JMS\Type("DateTime<'U'>")
     */
    protected \DateTime $untilDate;

    /**
     * @JMS\Expose()
     */
    protected int $altitude;

    /**
     * @JMS\Expose()
     */
    protected string $stationType;

    /**
     * @JMS\Expose()
     */
    protected string $areaType;

    /**
     * @JMS\Expose()
     */
    protected string $provider;

    /**
     * @JMS\Expose()
     */
    protected string $network;

    public function getStationCode(): ?string
    {
        return $this->stationCode;
    }

    public function setStationCode(string $stationCode): Station
    {
        $this->stationCode = $stationCode;

        return $this;
    }

    public function getUbaStationId(): ?int
    {
        return $this->ubaStationId;
    }

    public function setUbaStationId(int $ubaStationId): Station
    {
        $this->ubaStationId = $ubaStationId;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): Station
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): Station
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title = null): Station
    {
        $this->title = $title;

        return $this;
    }

    public function setCity(string $cityName = null): Station
    {
        $this->cityName = $cityName;

        return $this;
    }

    public function getCity(): string
    {
        return $this->cityName;
    }

    public function getFromDate(): ?\DateTime
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTime $fromDate = null): Station
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getFromDateFormatted(): ?string
    {
        return $this->fromDate ? $this->fromDate->format('Y-m-d H:i:s') : null;
    }

    public function getUntilDate(): ?\DateTime
    {
        return $this->untilDate;
    }

    public function setUntilDate(\DateTime $untilDate = null): Station
    {
        $this->untilDate = $untilDate;

        return $this;
    }

    public function getUntilDateFormatted(): ?string
    {
        return $this->untilDate ? $this->untilDate->format('Y-m-d H:i:s') : null;
    }

    public function getAltitude(): ?int
    {
        return $this->altitude;
    }

    public function setAltitude(int $altitude): Station
    {
        $this->altitude = $altitude;

        return $this;
    }

    public function getStationType(): ?string
    {
        return $this->stationType;
    }

    public function setStationType(string $stationType = null): Station
    {
        $this->stationType = $stationType;

        return $this;
    }

    public function getAreaType(): ?string
    {
        return $this->areaType;
    }

    public function setAreaType(string $areaType = null): Station
    {
        $this->areaType = $areaType;

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): Station
    {
        $this->provider = $provider;

        return $this;
    }

    public function getNetwork(): ?Network
    {
        return $this->network;
    }

    public function setNetwork(Network $network): Station
    {
        $this->network = $network;

        return $this;
    }
}
