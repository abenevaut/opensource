<?php

namespace abenevaut\BlueSky;

use abenevaut\Infrastructure\Client\AccessTokenInterface;

final class AccessToken implements AccessTokenInterface
{
    public function __construct(
        private readonly BlueSkyAnonymousClient $client,
        private readonly string $handle,
        private readonly string $password
    ) {
    }

    public function getAccessToken(): string
    {
        $response = $this->client->resolveHandler($this->handle);
        $response = $this->client->createSession($response['did'], $this->password);

        return "Bearer {$response['accessJwt']}";
    }
}
