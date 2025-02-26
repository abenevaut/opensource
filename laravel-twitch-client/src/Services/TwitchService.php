<?php

namespace abenevaut\Twitch\Services;

use abenevaut\Twitch\Client\TwitchClient;

final class TwitchService
{
    public function __construct(
        private readonly TwitchClient $client
    ) {
    }

    public function getClient(): TwitchClient
    {
        return $this->client;
    }

    public function countFollowers(string $broadcaster): int
    {
        $response = $this->getClient()->getUser($broadcaster);
        $broadcasterId = $response['data'][0]['id'];
        $response = $this->getClient()->getChannel($broadcasterId);

        return $response['total'];
    }
}
