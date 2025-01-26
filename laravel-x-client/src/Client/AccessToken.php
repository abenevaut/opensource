<?php

namespace abenevaut\X;

final class AccessToken
{
    public function getAccessToken(): string {
        return 'Bearer ' . $this->accessToken;
    }
}
