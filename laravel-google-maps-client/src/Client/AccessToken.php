<?php

namespace abenevaut\GoogleMaps\Client;

use abenevaut\Infrastructure\Client\AccessTokenInterface;

final class AccessToken implements AccessTokenInterface
{
    public function __construct(
        private readonly string $apiKey
    ) {
    }

    public function getAccessToken(): string
    {
        return $this->apiKey;
    }
}
