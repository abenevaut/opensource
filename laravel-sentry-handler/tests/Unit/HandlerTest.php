<?php

namespace Tests\Unit;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use Psr\Log\LoggerInterface;
use Sentry\Laravel\Facade as SentryFacade;
use Sentry\State\HubInterface;
use Tests\TestCase;

class HandlerTest extends TestCase
{
    protected $app;

    /**
     * @runInSeparateProcess
     */
    public function testToReportStandardException()
    {
        // test case exception
        $exception = new \Exception('report to sentry');

        $mock = $this->app->make(LoggerInterface::class);
        $mock->shouldReceive('error')->with($exception->getMessage(), \Mockery::any());

        $mock = $this->app->make(ExceptionHandler::class);
        $mock->shouldReceive('shouldReport')->with($exception)->andReturnTrue()->once();

        $mock = $this->app->make(HubInterface::class);
        $mock->shouldReceive('captureException')->with($exception)->once();

        // test
        $this->app->make(ExceptionHandler::class)->report($exception);
    }

    /**
     * @runInSeparateProcess
     */
    public function testToReportScopedException()
    {
        $exception = \Mockery::mock(\abenevaut\SentryHandler\Contracts\ExceptionAbstract::class)
            ->makePartial();
        $exception->shouldReceive('report')->once();

        $mock = $this->app->make(ExceptionHandler::class);
        $mock->shouldReceive('shouldReport')->with($exception)->andReturnTrue()->once();

        $mock = $this->app->make(HubInterface::class);
        $mock->shouldReceive('captureException')->with($exception)->once();

        // report
        $this->app->make(ExceptionHandler::class)->report($exception);

        $this->assertFalse($exception->isSentryMessage());
    }

    protected function setUp(): void
    {
        $this->app = new Application();

        /*
         * Mock ExceptionHandler
         */
        $mock = \Mockery::mock('\abenevaut\SentryHandler\Handler[isSentryBounded,shouldReport]', [$this->app]);
        $mock->shouldReceive('isSentryBounded')->andReturnTrue()->once();

        $this->app->instance(ExceptionHandler::class, $mock);

        /*
         * Mock HubInterface to use with Sentry Facade
         */
        $this->app->instance(HubInterface::class, \Mockery::mock('Sentry\State\HubInterface[captureException]'));
        AliasLoader::getInstance()->alias(HubInterface::class, SentryFacade::class);

        /*
         * Mock LoggerInterface
         */
        $this->app->instance(LoggerInterface::class, \Mockery::mock(LoggerInterface::class));

        /*
         * Mock config service
         */
        $this->app->instance('config', \Mockery::mock(\Illuminate\Config\Repository::class));

        Facade::setFacadeApplication($this->app);
    }
}
