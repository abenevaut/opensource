<?php

namespace abenevaut\Asana\Contracts;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

abstract class ApiRepositoryAbstract
{
    /**
     * @var string
     */
    private string $baseUrl = 'https://app.asana.com/api/1.0';

    public function __construct(
        private readonly string $accessToken,
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
    protected function request(): PendingRequest
    {
        return $this
            ->withHeaders()
            ->retry(3, 100);
    }

    /**
     * @return PendingRequest
     */
    private function withHeaders(): PendingRequest
    {
        return Http::withToken($this->accessToken)
            ->acceptJson();
    }
}
