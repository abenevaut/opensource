<?php

namespace abenevaut\Kite\App\Providers;

use Illuminate\Support\ServiceProvider;

class KiteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../../migrations');
        $this->mergeConfigFrom(__DIR__ . '/../../../config/auth.php', 'auth');
    }
}
