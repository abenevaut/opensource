<?php

namespace abenevaut\Asana\Facades;

use abenevaut\Asana\Contracts\AsanaProviderNameInterface;
use Illuminate\Support\Facades\Facade;

class Asana extends Facade implements AsanaProviderNameInterface
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return self::ASANA;
    }
}
