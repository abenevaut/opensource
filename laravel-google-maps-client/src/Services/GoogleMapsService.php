<?php

namespace abenevaut\GoogleMaps\Services;

use abenevaut\GoogleMaps\Client\GoogleMapsClient;

final class GoogleMapsService
{
    public function __construct(
        private readonly GoogleMapsClient $client
    ) {
    }

    public function getClient(): GoogleMapsClient
    {
        return $this->client;
    }
}
