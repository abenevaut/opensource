<?php

namespace abenevaut\GoogleMaps\Services;

use abenevaut\GoogleMaps\Client\TimezonesClient;
use abenevaut\GoogleMaps\Infrastructure\GoogleMapsServiceInterface;
use abenevaut\Infrastructure\Client\{AccessTokenInterface, ClientAbstract};

final class TimezonesService implements GoogleMapsServiceInterface
{
    protected TimezonesClient $client;

    public function __construct(
        AccessTokenInterface $accessToken,
        bool $debug = false
    ) {
        $this->client = new TimezonesClient($accessToken, $debug);
    }

    public function getClient(): ClientAbstract
    {
        return $this->client;
    }
}
