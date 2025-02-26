<?php

namespace abenevaut\Instagram\Facades;

use abenevaut\Instagram\Services\InstagramService;
use Illuminate\Support\Facades\Facade;

class Instagram extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InstagramService::class;
    }
}
