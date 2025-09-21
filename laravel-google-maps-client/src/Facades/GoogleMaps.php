<?php

namespace abenevaut\GoogleMaps\Facades;

use abenevaut\GoogleMaps\Client\GoogleMapsClient as GoogleMapsClient;
use Illuminate\Support\Facades\Facade;

class GoogleMaps extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GoogleMapsClient::class;
    }
}
