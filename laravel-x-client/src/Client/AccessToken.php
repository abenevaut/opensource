<?php

namespace abenevaut\X\Client;

use abenevaut\Infrastructure\Client\AccessTokenInterface;

final class AccessToken implements AccessTokenInterface
{
    public function __construct(
        private readonly XAnonymousClient $client,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {
    }

    public function getAccessToken(): string
    {
        $response = $this
            ->client
            ->getAccessToken(
                $this->clientId,
                $this->clientSecret
            );

        return "Bearer {$response['access_token']}";
    }
}
