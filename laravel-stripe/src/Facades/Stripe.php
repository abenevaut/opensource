<?php

namespace abenevaut\Stripe\Facades;

use abenevaut\Stripe\Contracts\StripeProviderNameInterface;
use Illuminate\Support\Facades\Facade;

class Stripe extends Facade implements StripeProviderNameInterface
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return self::STRIPE;
    }
}
