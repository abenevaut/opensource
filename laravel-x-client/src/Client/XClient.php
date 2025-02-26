<?php

namespace abenevaut\X\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;

final class XClient extends AuthenticatedClientAbstract
{
    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getUser(string $account): array
    {
        return $this
            ->request()
            ->get("/users/by/username/{$account}", [
                'user.fields' => 'public_metrics',
            ])
            ->throw()
            ->json();
    }
}
