<?php

namespace abenevaut\Twitch\Facades;

use abenevaut\Twitch\Services\TwitchService;
use Illuminate\Support\Facades\Facade;

class Twitch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TwitchService::class;
    }
}
