<?php

namespace abenevaut\Ohdear\Providers;

use abenevaut\Ohdear\Commands\ListUptimeCommand;
use abenevaut\Ohdear\Contracts\OhdearProviderNameInterface;
use abenevaut\Ohdear\Factories\OhdearDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class OhdearServiceProvider extends ServiceProvider implements OhdearProviderNameInterface
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
        $this
            ->registerRoutes()
            ->registerCommands()
            ->registerMacros();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(self::OHDEAR, function (Application $app) {
            // @codeCoverageIgnoreStart
            return new OhdearDriverFactory($app);
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
        // @codeCoverageIgnoreStart
        return [self::OHDEAR];
        // @codeCoverageIgnoreEnd
    }

    protected function registerRoutes(): self
    {
        if (
            $this->app->runningInConsole() === false
            && $this->app->runningUnitTests() === false
        ) {
            // @codeCoverageIgnoreStart
            $this->app['router']->group([
                'as' => 'ohdear.',
                'namespace' => 'abenevaut\Ohdear\Controllers',
            ], function () {
                $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
            });
            // @codeCoverageIgnoreEnd
        }

        return $this;
    }

    protected function registerCommands(): self
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                ListUptimeCommand::class,
            ]);
        }

        return $this;
    }

    protected function registerMacros(): self
    {
        Collection::macro('toOhdearEntity', function (string $class) {
            return $this->map(function ($value) use ($class) {
                return new $class($value);
            });
        });

        return $this;
    }
}
