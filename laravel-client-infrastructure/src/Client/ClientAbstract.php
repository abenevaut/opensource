<?php

namespace abenevaut\Infrastructure\Client;

use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Client\PendingRequest;

abstract class ClientAbstract
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly AccessTokenInterface|null $accessToken = null,
        private readonly bool $debug = false
    ) {
    }

    protected function request(array $requestHeaders = []): PendingRequest
    {
        $pendingRequest = $this
            ->withHeaders($requestHeaders)
            ->baseUrl($this->baseUrl)
            ->retry(3, 100);

        if ($this->debug) {
            $pendingRequest->withoutVerifying();
        }

        return $pendingRequest;
    }

    protected function withHeaders(array $requestHeaders = []): PendingRequest
    {
        /** @var PendingRequest $pendingRequest */
        $pendingRequest = (new HttpClientFactory())
            ->acceptJson()
            ->contentType('application/json')
            ->withHeaders(array_merge($this->getDefaultHeaders(), $requestHeaders));

        $this->authenticate($pendingRequest);

        return $pendingRequest;
    }

    protected function authenticate(PendingRequest $pendingRequest): void
    {
        if ($this->accessToken) {
            $pendingRequest->withHeader('Authorization', $this->accessToken->getAccessToken());
        }
    }

    protected function getDefaultHeaders(): array
    {
        return [];
    }
}
