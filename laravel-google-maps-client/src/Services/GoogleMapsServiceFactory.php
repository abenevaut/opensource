<?php

namespace abenevaut\GoogleMaps\Services;

final class GoogleMapsServiceFactory
{
    public function __construct(
        protected readonly array $applicationResolver,
        protected readonly array $drivers
    ) {
    }

    public function service(string $driver): object
    {
        if (!in_array($driver, $this->drivers)) {
            throw new \RuntimeException("Driver doesn't exist.");
        }

        $driver = ucfirst($driver);

        return $this->resolve("{$driver}Service");
    }

    protected function resolve(string $driver): object
    {
        return call_user_func($this->applicationResolver, $driver);
    }
}
