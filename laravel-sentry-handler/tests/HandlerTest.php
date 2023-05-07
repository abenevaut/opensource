<?php

namespace Tests;

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

    public function testToReportSentryWithStandardException()
    {
        // test case exception
        $exception = new \Exception('report to sentry');

        $mock = \Mockery::mock(LoggerInterface::class);
        $mock->makePartial();
        $mock->expects()->log(\Mockery::any(), $exception->getMessage(), \Mockery::any())->once();
        // we force the logger in app to allows the main Exception Handler to log exceptions
        $this->app->instance(LoggerInterface::class, $mock);

        $mock = $this->app->make(HubInterface::class);
        $mock->expects()->shouldNotHaveReceived('captureException');

        // test
        $this->app->make(ExceptionHandler::class)->report($exception);
    }

//    public function testToReportSentryWithSentryScopedException()
//    {
//        $exception = \Mockery::mock(\abenevaut\SentryHandler\Contracts\ExceptionAbstract::class);
//
////        $exception->shouldHaveReceived('report');
//
//
//        $mock = $this->app->make(HubInterface::class);
//        $mock->shouldHaveReceived('captureException');
//
//        $this->app->make(ExceptionHandler::class)->report($exception);
//    }

    protected function setUp(): void
    {
        $this->app = new Application();

        /*
         * Mock ExceptionHandler
         */
        $mock = \Mockery::mock('\abenevaut\SentryHandler\Handler[isSentryBounded,shouldReport]', [$this->app])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
        ;
        $mock->shouldReceive('isSentryBounded')->andReturnTrue();
        $mock->shouldReceive('shouldReport')->andReturnTrue();

        $this->app->instance(ExceptionHandler::class, $mock);

        /*
         * Mock HubInterface to use with Sentry Facade
         */
        $mock = \Mockery::spy('Sentry\State\HubInterface[captureException]');
        $mock->shouldReceive('captureException');

        $this->app->instance(HubInterface::class, $mock);

        /*
         * Set config service
         */
        $this->app->instance('config', new \Illuminate\Config\Repository());

        $loader = AliasLoader::getInstance();
        $loader->alias(HubInterface::class, SentryFacade::class);

        Facade::setFacadeApplication($this->app);
    }

    protected function tearDown(): void
    {
        \Mockery::close();

        parent::tearDown();
    }
}
