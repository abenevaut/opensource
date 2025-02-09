<?php

namespace abenevaut\Twitch\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;

final class TwitchClient extends AuthenticatedClientAbstract
{
    public function __construct(
        string $baseUrl,
        AccessToken $accessToken,
        bool $debug = false
    ) {
        parent::__construct($baseUrl, $accessToken, $debug);
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getUser(string $broadcaster): array
    {
        return $this
            ->request([
                'Client-ID' => $this->accessToken->getClientId(),
                'Authorization' => "Bearer {$this->accessToken}",
            ])
            ->get("/users?login={$broadcaster}")
            ->throw()
            ->json();
    }

    public function getChannel(string $broadcasterId): array
    {
        return $this
            ->request([
                'Client-ID' => $this->accessToken->getClientId(),
                'Authorization' => "Bearer {$this->accessToken}",
            ])
            ->get("/channels/followers/?broadcaster_id={$broadcasterId}")
            ->throw()
            ->json();
    }
}
