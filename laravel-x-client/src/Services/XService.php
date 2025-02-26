<?php

namespace abenevaut\X\Services;

use abenevaut\X\Client\XClient;

final class XService
{
    public function __construct(
        private readonly XClient $client
    ) {
    }

    public function getClient(): XClient
    {
        return $this->client;
    }

    public function countFollowers(string $account): int
    {
        $response = $this->getClient()->getUser($account);

        return $response['data']['public_metrics']['followers_count'];
    }
}
