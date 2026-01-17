<?php

namespace abenevaut\GoogleMaps\Services;

final class GoogleMapsServiceFactory
{
    public function __construct(
        protected readonly array $applicationResolver,
        private readonly array $services
    ) {
    }

    public function services(): array
    {
        return $this->services;
    }

    public function service(string $driver): object
    {
        if (!in_array($driver, $this->services())) {
            throw new \RuntimeException("Driver doesn't exist.");
        }

        $driver = ucfirst($driver);

        return $this->resolve("{$driver}Service");
    }

    public function classResolution(string $driver): string
    {
        if (!in_array($driver, $this->services())) {
            throw new \RuntimeException("Driver doesn't exist.");
        }

        $driver = ucfirst($driver);

        return "\abenevaut\GoogleMaps\Services\{$driver}Service";
    }

    protected function resolve(string $driver): object
    {
        return call_user_func($this->applicationResolver, $driver);
    }
}
