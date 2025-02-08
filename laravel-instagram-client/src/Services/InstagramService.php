<?php

namespace abenevaut\Instagram\Services;

use abenevaut\Instagram\Client\InstagramAnonymousClient;

final class InstagramService
{
    public function __construct(
        private readonly InstagramAnonymousClient $client
    ) {
    }

    public function getClient(): InstagramAnonymousClient
    {
        return $this->client;
    }

    public function countFollowers(string $account): int
    {
        $response = $this->getClient()->getUserWebProfileInfo($account);

        return $response['data']['user']['edge_followed_by']['count'];
    }
}
