<?php

namespace Tests\Unit;

use abenevaut\Paypal\Contracts\PaypalProviderNameInterface;
use abenevaut\Paypal\Factories\PaypalDriverFactory;
use abenevaut\Paypal\Providers\PaypalServiceProvider;
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
        $app->register(PaypalServiceProvider::class);
        $this->assertArrayHasKey(PaypalServiceProvider::class, $app->getLoadedProviders());
    }

    public function testSingletonsAreCreatedWhenServiceProviderIsRegistered()
    {
        $app = new Application();
        $app->register(PaypalServiceProvider::class);
        $instance = $app->make(PaypalProviderNameInterface::PAYPAL);

        $this->assertInstanceOf(PaypalDriverFactory::class, $instance);
        $this->assertSame($instance, $app->make(PaypalProviderNameInterface::PAYPAL));
    }
}
