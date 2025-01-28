<?php

namespace abenevaut\Infrastructure\Client;

use Illuminate\Http\Client\PendingRequest;

abstract class AuthenticatedClientAbstract extends ClientAbstract
{
    public function __construct(
        string $baseUrl,
        protected readonly AccessTokenInterface|null $accessToken = null,
        bool $debug = false
    ) {
        parent::__construct($baseUrl, $debug);
    }

    protected function withHeaders(array $requestHeaders = []): PendingRequest
    {
        /** @var PendingRequest $pendingRequest */
        $pendingRequest = parent::withHeaders($requestHeaders);

        $this->authenticate($pendingRequest);

        return $pendingRequest;
    }

    protected function authenticate(PendingRequest $pendingRequest): void
    {
        if ($this->accessToken) {
            $pendingRequest->withHeader('Authorization', $this->accessToken->getAccessToken());
        }
    }
}
