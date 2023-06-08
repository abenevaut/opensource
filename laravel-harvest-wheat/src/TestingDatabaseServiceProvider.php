<?php

namespace abenevaut\HarvestWheat;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class TestingDatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TestingDatabaseService::class, function () {
            // @codeCoverageIgnoreStart
            return new TestingDatabaseService();
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->environment('testing')) {
            DB::listen(function (QueryExecuted $query) {
                $this->app->make(TestingDatabaseService::class)->pushQuery($query);
            });
            DB::whenQueryingForLongerThan(3, function (Connection $connection, QueryExecuted $query) {
                $this->app->make(TestingDatabaseService::class)->flagQuery($query);
            });
        }
    }

    public function provides()
    {
        return [
            TestingDatabaseService::class,
        ];
    }
}
