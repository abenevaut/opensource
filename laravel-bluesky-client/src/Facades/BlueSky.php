<?php

namespace abenevaut\BlueSky\Facades;

use abenevaut\BlueSky\Services\BlueSkyService;
use Illuminate\Support\Facades\Facade;

class BlueSky extends Facade
{
    protected static function getFacadeAccessor()
    {
        return BlueSkyService::class;
    }
}
