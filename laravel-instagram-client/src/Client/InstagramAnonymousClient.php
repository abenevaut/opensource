<?php

namespace abenevaut\Instagram\Client;

use abenevaut\Infrastructure\Client\ClientAbstract;

final class InstagramAnonymousClient extends ClientAbstract
{
    private string $userAgent = 'Instagram 76.0.0.15.395 Android (24/7.0; 640dpi; 1440x2560; samsung; SM-G930F; herolte; samsungexynos8890; en_US; 138226743)';

    /**
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function getUserWebProfileInfo(string $username): array
    {
        return $this
            ->request()
            ->get("/users/web_profile_info/?username={$username}")
            ->throw()
            ->json();
    }

    protected function getDefaultHeaders(): array
    {
        return [
            'User-Agent' => trim($this->userAgent),
        ];
    }
}
