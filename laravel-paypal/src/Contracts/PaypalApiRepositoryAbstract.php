<?php

namespace abenevaut\Paypal\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

abstract class PaypalApiRepositoryAbstract
{
    private string $baseUrl;

    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly bool $debug
    ) {
        $this->baseUrl = $this->debug
            ? 'https://api.sandbox.paypal.com'
            : 'https://api.paypal.com';
    }

    protected function makeUrl(string $uri): string
    {
        return "{$this->baseUrl}{$uri}";
    }

    protected function request(): PendingRequest
    {
        return $this
            ->withHeaders()
            ->retry(3, 100);
    }

    private function withHeaders(): PendingRequest
    {
        return Http::withToken($this->requestAccessToken())
            ->acceptJson();
    }

    private function requestAccessToken(): string
    {
        $ttl = $this->debug
            ? 60
            : 60 * 60;

        return Cache::tags(['paypal'])
            ->remember('access_token', $ttl, function () {
                return Http::withHeaders([
                    'Accept-Language' => 'en_US',
                ])
                    ->acceptJson()
                    ->withBasicAuth($this->username, $this->password)
                    ->post($this->makeUrl('/v1/oauth2/token'), [
                        'grant_type' => 'client_credentials',
                    ])
                    ->json()
                    ->access_token;
            });
    }
}
