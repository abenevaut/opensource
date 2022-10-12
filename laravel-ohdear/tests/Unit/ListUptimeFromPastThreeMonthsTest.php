<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Actions\ListUptimeFromPastThreeMonthsAction;
use abenevaut\Ohdear\Facades\Ohdear;
use abenevaut\Ohdear\Providers\OhdearServiceProvider;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class ListUptimeFromPastThreeMonthsTest extends TestCase
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

    public function testListUptimeFromPastThreeMonths()
    {
        $this
            ->app['config']
            ->shouldReceive('get')
            ->times(9)
            ->with('services.ohdear.access_token')
            ->andReturns('0123456789');
        $this
            ->app['config']
            ->shouldReceive('get')
            ->times(9)
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

        for ($i = 2; $i >= 0; $i--) {
            $currentMonth = CarbonImmutable::now()->subMonthsNoOverflow($i);
            $startOfMonth = $currentMonth->startOfMonth()->format('YmdHis');
            $endOfMonth = $currentMonth->endOfMonth();

            if ($endOfMonth->isFuture() === true) {
                $endOfMonth = Carbon::now()->subDay()->endOfDay();
            }

            if ($endOfMonth->isAfter($startOfMonth) === false) {
                continue;
            }

            foreach ($data as $id) {
                $uptime = Ohdear::fakeSitesUptimeHttp(
                    $id,
                    $startOfMonth,
                    $endOfMonth->format('YmdHis')
                );
                $uptimes->push($uptime->uptime_percentage);
            }
        }

        $action = new ListUptimeFromPastThreeMonthsAction();
        $action->execute($data->toArray());

        $this->assertSame($action->uptimes->count(), $uptimes->count());
        $this->assertSame($action->uptimes->pluck('uptime_percentage')->flatten()->avg(), $uptimes->avg());
    }
}