<?php

namespace abenevaut\BlueSky\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class BlueSkyAnonymousClient extends ClientAbstract
{
    /**
     * @seealso https://docs.bsky.app/docs/api/com-atproto-identity-resolve-handle
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function resolveHandler(string $handle): array
    {
        return $this
            ->request()
            ->get('/xrpc/com.atproto.identity.resolveHandle', [
                'handle' => $handle,
            ])
            ->throw()
            ->json();
    }

    /**
     * @seealso https://docs.bsky.app/docs/api/com-atproto-server-create-session
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function createSession(string $identifier, string $password): array
    {
        return $this
            ->request()
            ->post('/xrpc/com.atproto.server.createSession', [
                'identifier' => $identifier,
                'password' => $password,
            ])
            ->throw()
            ->json();
    }
}
