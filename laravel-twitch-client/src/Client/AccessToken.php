<?php

namespace abenevaut\Twitch;

final class AccessToken
{
    public function getAccessToken(): string {
        return 'Bearer ' . $this->accessToken;
    }
}
