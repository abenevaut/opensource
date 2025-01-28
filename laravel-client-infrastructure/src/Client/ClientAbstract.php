<?php

namespace abenevaut\Infrastructure\Client;

use Illuminate\Http\Client\Factory as HttpClientFactory;
use Illuminate\Http\Client\PendingRequest;

abstract class ClientAbstract
{
    public function __construct(
        protected readonly string $baseUrl,
        protected readonly bool $debug = false
    ) {
    }

    protected function getDefaultHeaders(): array
    {
        return [];
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

        return $pendingRequest;
    }
}
