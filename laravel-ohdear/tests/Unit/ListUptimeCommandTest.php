<?php

namespace Tests\Unit;

use abenevaut\Ohdear\Commands\ListUptimeCommand;
use abenevaut\Ohdear\Facades\Ohdear;
use abenevaut\Ohdear\Providers\OhdearServiceProvider;
use Carbon\Carbon;
use Illuminate\Filesystem\FilesystemServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Translation\TranslationServiceProvider;
use Illuminate\Validation\ValidationServiceProvider;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class ListUptimeCommandTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        $this->app = new Application();
        $this->app['config'] = m::mock(\stdClass::class);
        $this->app['env'] = 'testing';

        $this->app->register(OhdearServiceProvider::class);
        $this->app->register(ValidationServiceProvider::class);
        $this->app->register(TranslationServiceProvider::class);
        $this->app->register(FilesystemServiceProvider::class);

        $this->app->instance('path.config', __DIR__ . '/../config');
        $this->app->instance('path.storage', __DIR__ . '/../storage');
        $this->app->instance('path.lang', __DIR__ . '/../lang');

        $this->app->boot();

        $this
            ->app['config']
            ->shouldReceive('get')
            ->once()
            ->with('app.locale')
            ->andReturns('en');
        $this
            ->app['config']
            ->shouldReceive('get')
            ->once()
            ->with('app.fallback_locale')
            ->andReturns('en');

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
        $this->markTestSkipped('waiting https://github.com/laravel/framework/pull/44521');

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

        $startOfWeek = Carbon::now()->startOfWeek()->format('YmdHis');
        $now = Carbon::now()->subHour()->format('YmdHis');

        foreach ($data as $id) {
            Ohdear::fakeSitesUptimeHttp(
                $id,
                $startOfWeek,
                $now
            );
        }

        $command = new ListUptimeCommand();
        $input = new ArrayInput([
            'from' => 'start_of_week',
            'sites' => $data->join(','),
        ]);
        $output = new NullOutput();

        $command->setLaravel($this->app);
        $returnStatement = $command->run($input, $output);

        $this->assertSame('start_of_week', $command->argument('from'));
        $this->assertSame($data->join(','), $command->argument('sites'));
        $this->assertSame(ListUptimeCommand::SUCCESS, $returnStatement);
    }
}