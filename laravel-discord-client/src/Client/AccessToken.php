<?php

namespace abenevaut\Discord;

final class AccessToken
{
    public function getAccessToken(): string {
        return 'Bearer ' . $this->accessToken;
    }
}
