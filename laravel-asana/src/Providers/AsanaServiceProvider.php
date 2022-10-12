<?php

namespace abenevaut\Asana\Providers;

use abenevaut\Asana\Contracts\AsanaProviderNameInterface;
use abenevaut\Asana\Factories\AsanaDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;

class AsanaServiceProvider extends ServiceProvider implements AsanaProviderNameInterface
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/asana.php',
        ], self::ASANA);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(self::ASANA, function (Application $app) {
            // @codeCoverageIgnoreStart
            return new AsanaDriverFactory($app);
            // @codeCoverageIgnoreEnd
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [self::ASANA];
    }
}
