<?php

namespace abenevaut\Linkedin\Facades;

use abenevaut\Linkedin\Services\LinkedinService;
use Illuminate\Support\Facades\Facade;

class Linkedin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return LinkedinService::class;
    }
}
