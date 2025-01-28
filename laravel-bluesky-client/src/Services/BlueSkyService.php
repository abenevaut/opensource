<?php

namespace abenevaut\BlueSky\Services;

use abenevaut\BlueSky\Client\BlueSkyClient;

final class BlueSkyService
{
    public function __construct(
        private readonly BlueSkyClient $client
    ) {
    }

    public function getClient(): BlueSkyClient
    {
        return $this->client;
    }

    public function countFollowers(string $account): int
    {
        $response = $this->getClient()->getProfile($account);

        return $response['profiles'][0]['followersCount'];
    }
}
