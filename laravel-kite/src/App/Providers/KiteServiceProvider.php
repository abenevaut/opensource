<?php

namespace abenevaut\Kite\App\Providers;

use abenevaut\Kite\App\Console\Commands\InitThemeCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KiteServiceProvider extends ServiceProvider
{
    protected $commands = [
        InitThemeCommand::class,
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../../config/auth.php', 'auth');
        $this->loadRoutes();
    }

    public function register(): void
    {
        $this->commands($this->commands);
    }

    public function schedule(Schedule $schedule): void
    {
    }

    protected function loadRoutes(): void
    {
        $routesPath = __DIR__ . '/../../../routes';

        if ($this->app->runningInConsole()) {
            return;
        }

        Route::middleware(['web', 'guest'])->group($routesPath . '/loginWebGuest.php');
        Route::middleware(['web', 'guest'])->group($routesPath . '/registerWebGuest.php');
        Route::middleware(['web', 'guest'])->group($routesPath . '/resetPasswordWebGuest.php');
        Route::middleware(['web', 'auth'])->group($routesPath . '/webAuthenticated.php');
    }
}