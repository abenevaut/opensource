<?php

namespace abenevaut\GoogleMaps\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;
use abenevaut\Infrastructure\Client\AccessTokenInterface;

final class TimezonesClient extends AuthenticatedClientAbstract
{
    public function __construct(
        AccessTokenInterface $accessToken,
        bool $debug = false
    ) {
        parent::__construct(
            'https://maps.googleapis.com/maps/api/timezone',
            $accessToken,
            $debug
        );
    }

    /**
     * https://developers.google.com/maps/documentation/timezone/requests-timezone?hl=fr
     *
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function searchTimezone(float $lat, float $lng, $timestamp): array
    {
        return $this
            ->request()
            ->get('/json', [
                'location' => "{$lat},{$lng}",
                'timestamp' => $timestamp,
                'key' => $this->accessToken->getAccessToken(),
            ])
            ->throw()
            ->json();
    }
}
