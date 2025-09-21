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

    protected function authenticate(PendingRequest $pendingRequest): void
    {
        if ($this->accessToken) {
            $pendingRequest->withHeader('X-Goog-Api-Key', $this->accessToken->getAccessToken());
        }
    }
}
