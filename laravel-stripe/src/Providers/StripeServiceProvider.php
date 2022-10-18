<?php

namespace abenevaut\Stripe\Providers;

use abenevaut\Stripe\Contracts\StripeEntitiesEnum;
use abenevaut\Stripe\Contracts\StripeProviderNameInterface;
use abenevaut\Stripe\Factories\StripeDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class StripeServiceProvider extends ServiceProvider implements StripeProviderNameInterface
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
            __DIR__.'/../../config/abenevaut.php',
        ], self::STRIPE);

        Collection::macro('toStripeEntity', function (StripeEntitiesEnum $driver) {
            return $this->map(function ($value) use ($driver) {
                return new ("abenevaut\\Stripe\\Entities\\{$driver->value}Entity")($value);
            });
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();

        $this->app->singleton(self::STRIPE, function (Application $app) {
            // @codeCoverageIgnoreStart
            return new StripeDriverFactory($app);
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
        return [self::STRIPE];
    }
}
