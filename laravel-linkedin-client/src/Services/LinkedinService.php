<?php

namespace abenevaut\Linkedin\Services;

use abenevaut\Linkedin\Client\LinkedinClient;

final class LinkedinService
{
    public function __construct(
        private readonly LinkedinClient $client
    ) {
    }

    public function getClient(): LinkedinClient
    {
        return $this->client;
    }

    public function countGroupMembers(string $groupId): int
    {
        $response = $this->getClient()->getGroup($groupId, ['id', 'numMembers']);

        return $response['numMembers'];
    }

    public function countCompanyFollowers(string $companyId): int
    {
        $response = $this->getClient()->getCompanyFollowerStatistics($companyId);

        return $response['elements'][0]['followerCounts']['organicFollowerCount'];
    }
}
