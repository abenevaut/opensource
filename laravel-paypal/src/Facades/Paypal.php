<?php

namespace abenevaut\Paypal\Facades;

use abenevaut\Paypal\Contracts\PaypalProviderNameInterface;
use Illuminate\Support\Facades\Facade;

class Paypal extends Facade implements PaypalProviderNameInterface
{
    protected static function getFacadeAccessor()
    {
        return self::PAYPAL;
    }
}
