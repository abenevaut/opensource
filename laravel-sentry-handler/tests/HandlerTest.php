<?php

namespace Tests;

use DG\BypassFinals;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Sentry\Laravel\Facade as SentryFacade;
use Sentry\State\HubInterface;

class HandlerTest extends TestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        BypassFinals::enable();

        $this->app = new Application();

        /*
         * Mock ExceptionHandler
         */
        $mock = \Mockery::mock('\abenevaut\SentryHandler\Handler[isSentryBounded,shouldReport]', [$this->app]);
        $mock->shouldReceive('isSentryBounded')->andReturnTrue();
        $mock->shouldReceive('shouldReport')->andReturnTrue();

        $this->app->instance(ExceptionHandler::class, $mock);

        /*
         * Mock HubInterface to use with Sentry Facade
         */
        $mock = \Mockery::spy('Sentry\State\HubInterface[captureException]');
        $mock->shouldReceive('captureException');

        $this->app->instance(HubInterface::class, $mock);

        $loader = AliasLoader::getInstance();
        $loader->alias(HubInterface::class, SentryFacade::class);

        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }

//    public function testToReportSentryWithStandardException()
//    {
//        // test case exception
//        $exception = new \Exception('report to sentry');
//
//        $mock = \Mockery::mock(LoggerInterface::class);
//        $mock->expects()->log(\Mockery::any(), $exception->getMessage(), \Mockery::any())->once();
//        // we force the logger in app to allows the main Exception Handler to log exceptions
//        $this->app->instance(LoggerInterface::class, $mock);
//
//        // test
//        $this->app->make(ExceptionHandler::class)->report($exception);
//    }

    public function testToReportSentryWithSentryScopedException()
    {
        $exception = \Mockery::mock('\abenevaut\SentryHandler\Contracts\ExceptionAbstract', \Throwable::class)
            ->shouldReceive('report')
            ->getMock()
            ->shouldReceive('getMessage');

        // test
        $this->app->make(ExceptionHandler::class)->report($exception);
    }
}
