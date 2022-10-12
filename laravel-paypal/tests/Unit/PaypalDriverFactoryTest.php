<?php

namespace Tests\Unit;

use abenevaut\Paypal\Contracts\PaypalDriversEnum;
use abenevaut\Paypal\Factories\PaypalDriverFactory;
use abenevaut\Paypal\Repositories\AchievementsRepository;
use Illuminate\Foundation\Application;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Foundation/FoundationApplicationTest.php
 */
class PaypalDriverFactoryTest extends TestCase
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
            ->with('abenevaut.endpoint')
            ->andReturns('https://api.benevaut.test');
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
        $factory = new PaypalDriverFactory($this->app);
        $instance = $factory->request(PaypalDriversEnum::ACHIEVEMENTS);

        $this->assertInstanceOf(AchievementsRepository::class, $instance);
    }
}
