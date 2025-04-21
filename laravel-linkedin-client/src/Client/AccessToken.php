<?php

namespace abenevaut\Linkedin\Client;

use abenevaut\Infrastructure\Client\AccessTokenInterface;

final class AccessToken implements AccessTokenInterface
{
    public function __construct(
        private readonly LinkedinAnonymousClient $client,
        private readonly string $clientId,
        private readonly string $clientSecret
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
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
