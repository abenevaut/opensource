<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Facades\Ohdear;
use abenevaut\Ohdear\Providers\OhdearServiceProvider;
use abenevaut\Ohdear\Repositories\SitesRepository;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * https://github.com/laravel/framework/blob/9.x/tests/Foundation/FoundationApplicationTest.php
 */
class SitesRepositoryTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app['env'] = 'testing';
        $this->app->register(OhdearServiceProvider::class);
        $this->app->boot();
        Facade::setFacadeApplication($this->app);

        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    public function testAll()
    {
        Ohdear::fakeSitesHttp(
            $currentPage = 1,
            $total = 12,
            $perPage = 5
        );

        $instance = new SitesRepository('0123456789', true);
        $resources = $instance->all();

        $this->assertNotEmpty($resources->getCollection());
        $this->assertSame($total, $resources->total());
        $this->assertSame($perPage, $resources->perPage());
        $this->assertSame($currentPage, $resources->currentPage());
        $this->assertSame(null, $resources->previousPageUrl());
        $this->assertSame("https://ohdear.app/api/sites?page%5Bnumber%5D=2", $resources->nextPageUrl());
    }
}
