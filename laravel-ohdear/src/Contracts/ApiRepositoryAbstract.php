<?php

namespace abenevaut\Ohdear\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class ApiRepositoryAbstract
{
    private string $baseUrl = 'https://ohdear.app/api';

    public function __construct(
        private readonly string $accessToken,
        private readonly bool $debug
    ) {
    }

    protected function makeUrl(string $uri): string
    {
        return "{$this->baseUrl}{$uri}";
    }

    protected function request(array $requestHeaders = []): PendingRequest
    {
        $pendingRequest = $this->withHeaders($requestHeaders);

        if ($this->debug) {
            $pendingRequest->withoutVerifying();
        }

        return $pendingRequest->retry(3, 100);
    }

    private function withHeaders(array $requestHeaders = []): PendingRequest
    {
        $defaultHeaders = [];

        return Http::withHeaders(array_merge($defaultHeaders, $requestHeaders))
            ->withToken($this->accessToken)
            ->acceptJson();
    }
}
