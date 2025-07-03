<?php

namespace LaravelSqlMap;

use Illuminate\Support\ServiceProvider;
use LaravelSqlMap\Console\Commands\GenerateSqlMapFiles;

class SqlMapServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sqlmap.php', 'sqlmap'
        );
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateSqlMapFiles::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/sqlmap.php' => config_path('sqlmap.php'),
            ], 'sqlmap-config');

            $this->publishes([
                __DIR__.'/Http/Middleware/ValidateCsrfToken.php' => app_path('Http/Middleware/ValidateCsrfToken.php'),
            ], 'sqlmap-middleware');
        }
    }
}
