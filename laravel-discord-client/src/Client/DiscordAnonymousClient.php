<?php

namespace abenevaut\Discord\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class DiscordAnonymousClient extends ClientAbstract
{
    /**
     * @seealso https://discord.com/developers/docs/resources/invite#get-invite
     * @throws \Illuminate\Http\Client\ConnectionException
     */
    public function getInvite(string $invitationId): array
    {
        return $this
            ->request()
            ->get("/v9/invites/{$invitationId}?with_counts=true&with_expiration=true")
            ->throw()
            ->json();
    }
}
