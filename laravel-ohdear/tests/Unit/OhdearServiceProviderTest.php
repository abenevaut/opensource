<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Contracts\OhdearProviderNameInterface;
use abenevaut\Ohdear\Factories\OhdearDriverFactory;
use abenevaut\Ohdear\Providers\OhdearServiceProvider;
use Illuminate\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class OhdearServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testServiceProvidersAreCorrectlyRegistered()
    {
        $app = new Application();
        $app->register(OhdearServiceProvider::class);
        $this->assertArrayHasKey(OhdearServiceProvider::class, $app->getLoadedProviders());
    }

    public function testSingletonsAreCreatedWhenServiceProviderIsRegistered()
    {
        $app = new Application();
        $app->register(OhdearServiceProvider::class);
        $instance = $app->make(OhdearProviderNameInterface::OHDEAR);

        $this->assertInstanceOf(OhdearDriverFactory::class, $instance);
        $this->assertSame($instance, $app->make(OhdearProviderNameInterface::OHDEAR));
    }
}
