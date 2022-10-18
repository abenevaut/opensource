<?php

namespace abenevaut\Stripe\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class StripeRepositoryAbstract
{
    public function __construct(
        private readonly string $publicKey,
        private readonly string $secretKey,
        private readonly bool $debug
    ) {
    }

    /**
     * @param  string  $uri
     * @return string
     */
    protected function makeUrl(string $uri): string
    {
        return "{$this->baseUrl}{$uri}";
    }

    /**
     * @return PendingRequest
     */
    protected function request(bool $withToken = false, array $requestHeaders = []): PendingRequest
    {
        $pendingRequest = $this->withHeaders($withToken, $requestHeaders);

        if ($this->debug) {
            $pendingRequest->withoutVerifying();
        }

        return $pendingRequest->retry(3, 100);
    }

    /**
     * @param  bool  $withToken
     * @param  array  $requestHeaders
     * @return PendingRequest
     */
    private function withHeaders(bool $withToken = false, array $requestHeaders = []): PendingRequest
    {
        $defaultHeaders = [];
        $pendingRequest = Http::withHeaders(array_merge($defaultHeaders, $requestHeaders));

        if ($withToken) {
            $pendingRequest->withToken(config('abenevaut.access_token'));
        }

        return $pendingRequest;
    }
}
