<?php

namespace abenevaut\Twitch\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class TwitchAnonymousClient extends ClientAbstract
{
    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAccessToken(string $clientId, string $clientSecret): array
    {
        return $this
            ->request([
                'Accept-Language' => 'en_US',
            ])
            ->post('/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ])
            ->throw()
            ->json();
    }
}
