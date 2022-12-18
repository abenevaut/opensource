<?php

namespace abenevaut\Session\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IdentifyRequestMiddleware
{
    public function handle($request, Closure $next)
    {
        $requestHitId = (string) Str::uuid();

        Log::shareContext([
            'request-hit-id' => $requestHitId,
            'user-id' => $request?->user()?->id,
            'session-id' => $request->session()->getId(),
        ]);

        return $next($request)
            ->header('Request-Hit-Id', $requestHitId);
    }
}
