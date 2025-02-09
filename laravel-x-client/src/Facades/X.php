<?php

namespace abenevaut\X\Facades;

use abenevaut\X\Services\XService;
use Illuminate\Support\Facades\Facade;

class X extends Facade
{
    protected static function getFacadeAccessor()
    {
        return XService::class;
    }
}
