<?php

namespace Tests;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\DatabaseManager as Database;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected Database $database;

    public function setUp(): void
    {
        parent::setUp();

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'   => 'sqlite',
            'host'     => 'localhost',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        /*
         * Should looks like `vendor/illuminate/session/Console/stubs/database.stub`
         */
        Capsule::schema()->create('sessions', function ($table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        $this->database = $capsule->getDatabaseManager();

        Config::shouldReceive('get')
            ->once()
            ->with('session.connection')
            ->andReturn('default');
    }
}
