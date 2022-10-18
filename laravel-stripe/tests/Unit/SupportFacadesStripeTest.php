<?php

namespace Tests\Unit;

use abenevaut\Stripe\Contracts\StripeProviderNameInterface;
use abenevaut\Stripe\Facades\Stripe;
use abenevaut\Stripe\Factories\StripeDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesEventTest.php
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesHttpTest.php
 */
class SupportFacadesStripeTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->singleton(StripeProviderNameInterface::STRIPE, StripeDriverFactory::class);

        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        Stripe::clearResolvedInstances();
        Stripe::setFacadeApplication(null);

        m::close();
    }

    public function testFacadeRootIsBound(): void
    {
        $this->assertSame(Stripe::getFacadeRoot(), $this->app->make(StripeProviderNameInterface::STRIPE));
        $this->assertNotSame(Stripe::getFacadeRoot(), $this->app->make(StripeDriverFactory::class));
    }
}
