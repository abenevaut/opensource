<?php

namespace abenevaut\Paypal\Factories;

use abenevaut\Paypal\Contracts\PaypalApiRepositoryAbstract;
use abenevaut\Paypal\Contracts\PaypalDriversEnum;
use Illuminate\Foundation\Application;

class PaypalDriverFactory
{
    public function __construct(private Application $app)
    {
    }

    public function drive(PaypalDriversEnum $driver): PaypalApiRepositoryAbstract
    {
        return $this
            ->app
            ->make('\\abenevaut\\Paypal\\Repositories\\' . $driver->value . 'Repository', [
                'username' => $this->app['config']->get('service.paypal.username'),
                'password' => $this->app['config']->get('service.paypal.password'),
                'debug' => $this->app->hasDebugModeEnabled(),
            ]);
    }
}
