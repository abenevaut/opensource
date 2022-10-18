<?php

namespace abenevaut\Stripe\Factories;

use abenevaut\Stripe\Contracts\StripeRepositoryAbstract;
use abenevaut\Stripe\Contracts\StripeDriversEnum;
use Illuminate\Foundation\Application;

class StripeDriverFactory
{
    /**
     * @param  Application  $app
     */
    public function __construct(private Application $app)
    {
    }

    /**
     * @param  StripeDriversEnum  $driver
     * @return StripeRepositoryAbstract
     */
    public function request(StripeDriversEnum $driver): StripeRepositoryAbstract
    {
        return $this
            ->app
            ->make('\\abenevaut\\Stripe\\Repositories\\'.$driver->value.'Repository', [
                'publicKey' => $this->app['config']->get('service.stripe.public_key'),
                'secretKey' => $this->app['config']->get('service.stripe.secret_key'),
                'debug' => $this->app->hasDebugModeEnabled(),
            ]);
    }
}
