<?php

namespace abenevaut\HarvestWheat;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class TestingDatabaseServiceProvider extends ServiceProvider
{
    public static array $queries = [];
    public static int $nbQueries = 0;

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
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
                self::$nbQueries++;
            });

            DB::whenQueryingForLongerThan(2, function (Connection $connection, QueryExecuted $query) {
                self::$queries[] = [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ];
            });

        }
    }
}
