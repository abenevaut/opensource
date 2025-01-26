<?php

namespace abenevaut\BlueSky;

final class AccessToken
{
    public function getAccessToken(): string {
        return 'Bearer ' . $this->accessToken;
    }
}
