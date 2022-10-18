<?php

namespace Tests\Unit;

use abenevaut\Paypal\Contracts\PaypalProviderNameInterface;
use abenevaut\Paypal\Facades\Paypal;
use abenevaut\Paypal\Factories\PaypalDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesEventTest.php
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesHttpTest.php
 */
class SupportFacadesPaypalTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->singleton(PaypalProviderNameInterface::PAYPAL, PaypalDriverFactory::class);

        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        Paypal::clearResolvedInstances();
        Paypal::setFacadeApplication(null);

        m::close();
    }

    public function testFacadeRootIsBound(): void
    {
        $this->assertSame(Paypal::getFacadeRoot(), $this->app->make(PaypalProviderNameInterface::PAYPAL));
        $this->assertNotSame(Paypal::getFacadeRoot(), $this->app->make(PaypalDriverFactory::class));
    }
}
