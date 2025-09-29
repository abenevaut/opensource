<?php

namespace abenevaut\GoogleMaps\Client;

use abenevaut\Infrastructure\Client\AuthenticatedClientAbstract;
use abenevaut\Infrastructure\Client\AccessTokenInterface;
use Illuminate\Http\Client\PendingRequest;

final class GoogleMapsClient extends AuthenticatedClientAbstract
{
    public function __construct(
        string $baseUrl,
        AccessTokenInterface $accessToken,
        bool $debug = false
    ) {
        parent::__construct($baseUrl, $accessToken, $debug);
    }

    public function searchNearby(array $requestBody, array $fieldMaskParts = []): array
    {
        $response = $this->request();

        if (!empty($fieldMaskParts)) {
            $response->withHeaders([
                'X-Goog-FieldMask' => implode(',', $fieldMaskParts)
            ]);
        }

        return $response
            ->post('v1/places:searchNearby', $requestBody)
            ->throw()
            ->json();
    }

    /**
     * https://developers.google.com/maps/documentation/timezone/requests-timezone?hl=fr
     *
     * @return array
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function searchTimezone(float $lat, float, $lng, $timestamp): array
    {
        $response = $this->request();

        if (!empty($fieldMaskParts)) {
            $response->withHeaders([
                'X-Goog-FieldMask' => implode(',', $fieldMaskParts)
            ]);
        }

        return $response
            ->get('https://maps.googleapis.com/maps/api/timezone/json', [
                'location' => $lat . ',' . $lng,
                'timestamp' => $timestamp,
                'key' => $apiKey,
            ])
            ->throw()
            ->json();
    }

    protected function authenticate(PendingRequest $pendingRequest): void
    {
        if ($this->accessToken) {
            $pendingRequest->withHeader('X-Goog-Api-Key', $this->accessToken->getAccessToken());
        }
    }
}
