<?php

namespace abenevaut\BlueSky\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;

final class BlueSkyClient extends AuthenticatedClientAbstract
{
    /**
     * @seealso https://docs.bsky.app/docs/api/app-bsky-actor-get-profiles
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getProfile(string|array $actors): array
    {
        return $this
            ->request()
            ->get('/xrpc/app.bsky.actor.getProfiles', [
                'actors' => $actors,
            ])
            ->throw()
            ->json();
    }
}
