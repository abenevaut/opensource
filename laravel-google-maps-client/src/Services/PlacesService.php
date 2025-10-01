<?php

namespace abenevaut\GoogleMaps\Services;

use abenevaut\GoogleMaps\Client\PlacesClient;
use abenevaut\GoogleMaps\Infrastructure\GoogleMapsServiceInterface;
use abenevaut\Infrastructure\Client\{AccessTokenInterface, ClientAbstract};

final class PlacesService implements GoogleMapsServiceInterface
{
    protected PlacesClient $client;

    public function __construct(
        AccessTokenInterface $accessToken,
        bool $debug = false
    ) {
        $this->client = new PlacesClient($accessToken, $debug);
    }

    public function getClient(): ClientAbstract
    {
        return $this->client;
    }
}
