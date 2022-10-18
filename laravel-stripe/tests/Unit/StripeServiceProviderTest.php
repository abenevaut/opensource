<?php

namespace Tests\Unit;

use abenevaut\Stripe\Contracts\StripeProviderNameInterface;
use abenevaut\Stripe\Factories\StripeDriverFactory;
use abenevaut\Stripe\Providers\StripeServiceProvider;
use Illuminate\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class FoundationApplicationTest extends TestCase
{
    protected function tearDown(): void
    {
        m::close();
    }

    public function testServiceProvidersAreCorrectlyRegistered()
    {
        $app = new Application();
        $app->register(StripeServiceProvider::class);
        $this->assertArrayHasKey(StripeServiceProvider::class, $app->getLoadedProviders());
    }

    public function testSingletonsAreCreatedWhenServiceProviderIsRegistered()
    {
        $app = new Application();
        $app->register(StripeServiceProvider::class);
        $instance = $app->make(StripeProviderNameInterface::STRIPE);

        $this->assertInstanceOf(StripeDriverFactory::class, $instance);
        $this->assertSame($instance, $app->make(StripeProviderNameInterface::STRIPE));
    }
}
