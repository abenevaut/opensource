<?php

namespace abenevaut\X\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class XAnonymousClient extends ClientAbstract
{
    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getAccessToken(string $clientId, string $clientSecret): array
    {
        $basicToken = base64_encode("{$clientId}:{$clientSecret}");
        return $this
            ->request([
                'Authorization' => "Basic {$basicToken}",
            ])
            ->asForm()
            ->post('/oauth2/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                /*
                 * https://docs.x.com/resources/fundamentals/authentication/guides/v2-authentication-mapping
                 */
                'scope' => 'users.read tweet.read',
            ])
            ->throw()
            ->json();
    }
}
