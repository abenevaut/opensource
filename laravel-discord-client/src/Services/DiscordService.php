<?php

namespace abenevaut\Discord\Services;

use abenevaut\Discord\Client\DiscordAnonymousClient;

final class DiscordService
{
    public function __construct(
        private readonly DiscordAnonymousClient $client
    ) {
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function getClient(): DiscordAnonymousClient
    {
        return $this->client;
    }

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function countFollowers(string $invitationLink): int
    {
        $invitationId = $this->getInvitationIdFromInvitationLink($invitationLink);
        $response = $this->getClient()->getInvite($invitationId);

        return $response['approximate_member_count'];
    }

    /**
     * @throws \Exception
     */
    public function getInvitationIdFromInvitationLink(string $invitationLink): string
    {
        if (!str_contains($invitationLink, 'https://discord.gg')) {
            throw new \Exception('Invalid Discord invitation link.');
        }

        $invitationId = explode('/', $invitationLink);
        $invitationId = $invitationId[3] ?? '';

        if (empty($invitationId)) {
            throw new \Exception('Invalid Discord invitation link.');
        }

        if (preg_match('/[^a-zA-Z0-9-_]/', $invitationId)) {
            throw new \Exception('Invalid Discord invitation link.');
        }

        return $invitationId;
    }
}
