<?php

namespace abenevaut\Instagram;

final class AccessToken
{
    public function getAccessToken(): string {
        return 'Bearer ' . $this->accessToken;
    }
}
