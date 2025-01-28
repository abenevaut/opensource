<?php

namespace abenevaut\Discord\Facades;

use abenevaut\Discord\Services\DiscordService;
use Illuminate\Support\Facades\Facade;

class Discord extends Facade
{
    protected static function getFacadeAccessor()
    {
        return DiscordService::class;
    }
}
