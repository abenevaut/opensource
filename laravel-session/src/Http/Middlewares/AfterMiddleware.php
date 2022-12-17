<?php

namespace App\Http\Middleware;

use abenevaut\Session\App\Jobs\RecordTimeSpentOnAppByUserJob;
use Closure;
use Illuminate\Http\Request;

class AfterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        RecordTimeSpentOnAppByUserJob::dispatch($request, $response);

        return $response;
    }
}
