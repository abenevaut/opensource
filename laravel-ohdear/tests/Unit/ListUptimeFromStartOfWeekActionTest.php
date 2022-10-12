<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Actions\ListUptimeFromStartOfWeekAction;
use abenevaut\Ohdear\Facades\Ohdear;
use abenevaut\Ohdear\Providers\OhdearServiceProvider;
use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ListUptimeFromStartOfWeekActionTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app['config'] = m::mock(\stdClass::class);
        $this->app['env'] = 'testing';

        $this->app->register(OhdearServiceProvider::class);
        $this->app->boot();

        Facade::setFacadeApplication($this->app);

        Http::preventStrayRequests();

        Carbon::getTestNow();
    }

    protected function tearDown(): void
    {
        Ohdear::clearResolvedInstances();
        Ohdear::setFacadeApplication(null);

        m::close();
    }

    public function testListUptimeFromStartOfWeek()
    {
        $this
            ->app['config']
            ->shouldReceive('get')
            ->times(5)
            ->with('services.ohdear.access_token')
            ->andReturns('0123456789');
        $this
            ->app['config']
            ->shouldReceive('get')
            ->times(5)
            ->with('app.debug')
            ->andReturns(true);

        $data = Ohdear::fakeSitesHttp(
            $currentPage = 1,
            $total = 12,
            $perPage = 5
        );
        Ohdear::fakeSitesHttp(
            $currentPage = 2,
            $total = 12,
            $perPage = 5
        );
        Ohdear::fakeSitesHttp(
            $currentPage = 3,
            $total = 12,
            $perPage = 5
        );

        $data = collect($data)->splice(3)->pluck('id')->flatten();
        $uptimes = collect();
        $startOfWeek = Carbon::now()->startOfWeek()->format('YmdHis');
        $now = Carbon::now()->subHour()->format('YmdHis');

        foreach ($data as $id) {
            $uptime = Ohdear::fakeSitesUptimeHttp(
                $id,
                $startOfWeek,
                $now
            );
            $uptimes->push($uptime->uptime_percentage);
        }

        $action = new ListUptimeFromStartOfWeekAction();
        $action->execute($data->toArray());

        $this->assertSame($action->uptimes->avg(), $uptimes->avg());
    }
}