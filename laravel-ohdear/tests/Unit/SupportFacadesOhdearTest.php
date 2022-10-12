<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Contracts\OhdearProviderNameInterface;
use abenevaut\Ohdear\Facades\Ohdear;
use abenevaut\Ohdear\Factories\OhdearDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesEventTest.php
 * https://github.com/laravel/framework/blob/9.x/tests/Support/SupportFacadesHttpTest.php
 */
class SupportFacadesOhdearTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app->singleton(OhdearProviderNameInterface::OHDEAR, OhdearDriverFactory::class);

        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        Ohdear::clearResolvedInstances();
        Ohdear::setFacadeApplication(null);

        m::close();
    }

    public function testFacadeRootIsBound(): void
    {
        $this->assertSame(Ohdear::getFacadeRoot(), $this->app->make(OhdearProviderNameInterface::OHDEAR));
        $this->assertNotSame(Ohdear::getFacadeRoot(), $this->app->make(OhdearDriverFactory::class));
    }
}
