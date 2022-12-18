<?php

namespace abenevaut\Session\Http\Middleware;

use abenevaut\Session\App\Events\TimeSpentOnAppByUserEvent;
use Closure;
use Illuminate\Http\Request;

class AfterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        TimeSpentOnAppByUserEvent::dispatch($request, $response);

        return $response;
    }
}
