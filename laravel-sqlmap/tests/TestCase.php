<?php

namespace LaravelSqlMap\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use LaravelSqlMap\SqlMapServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            SqlMapServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
        config()->set('sqlmap.enabled', true);
        config()->set('sqlmap.disable_csrf', true);

        // Set encryption key for CSRF tests
        config()->set('app.key', 'base64:'.base64_encode(
            random_bytes(32)
        ));
    }
}
