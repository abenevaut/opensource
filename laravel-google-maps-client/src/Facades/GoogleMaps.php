<?php

namespace abenevaut\GoogleMaps\Facades;

use abenevaut\GoogleMaps\Services\GoogleMapsServiceFactory;
use Illuminate\Support\Facades\Facade;

class GoogleMaps extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GoogleMapsServiceFactory::class;
    }
}
