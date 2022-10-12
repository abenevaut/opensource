<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Contracts\OhdearDriversEnum;
use abenevaut\Ohdear\Factories\OhdearDriverFactory;
use abenevaut\Ohdear\Repositories\SitesRepository;
use Illuminate\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Foundation/FoundationApplicationTest.php
 */
class OhdearDriverFactoryTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app['config'] = m::mock(\stdClass::class);
        $this
            ->app['config']
            ->shouldReceive('get')
            ->once()
            ->with('services.ohdear.access_token')
            ->andReturns('0123456789');
        $this
            ->app['config']
            ->shouldReceive('get')
            ->once()
            ->with('app.debug')
            ->andReturns(true);
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testRequestAchievementsInstance()
    {
        $factory = new OhdearDriverFactory($this->app);
        $instance = $factory->request(OhdearDriversEnum::SITES);

        $this->assertInstanceOf(SitesRepository::class, $instance);
    }
}
