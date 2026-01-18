<?php

namespace abenevaut\Linkedin\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class LinkedinAnonymousClient extends ClientAbstract
{
    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAccessToken(string $clientId, string $clientSecret): array
    {
        return $this
            ->asForm()
            ->post('/accessToken', [
                'grant_type' => 'authorization_code',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ])
            ->throw()
            ->json();
    }
}
