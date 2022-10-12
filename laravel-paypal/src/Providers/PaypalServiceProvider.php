<?php

namespace abenevaut\Paypal\Providers;

use abenevaut\Paypal\Contracts\PaypalEntitiesEnum;
use abenevaut\Paypal\Contracts\PaypalProviderNameInterface;
use abenevaut\Paypal\Factories\PaypalDriverFactory;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class PaypalServiceProvider extends ServiceProvider implements PaypalProviderNameInterface
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    public function boot(): void
    {
        Collection::macro('toPaypalEntity', function (PaypalEntitiesEnum $driver) {
            return $this->map(function ($value) use ($driver) {
                return new ("abenevaut\\Paypal\\Entities\\{$driver->value}Entity")($value);
            });
        });
    }

    public function register(): void
    {
        parent::register();

        $this->app->singleton(self::PAYPAL, function (Application $app) {
            // @codeCoverageIgnoreStart
            return new PaypalDriverFactory($app);
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides(): array
    {
        return [self::PAYPAL];
    }
}
