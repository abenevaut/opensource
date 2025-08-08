<?php

namespace LaravelSqlMap\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken as Middleware;
use Illuminate\Http\Request;

class ValidateCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Skip CSRF validation if SQLMap testing is enabled and user agent matches
        if ($this->shouldBypassCsrfForSqlMap($request)) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine if CSRF validation should be bypassed for SQLMap testing.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldBypassCsrfForSqlMap(Request $request): bool
    {
        // Check if SQLMap CSRF bypass is enabled
        if (!config('sqlmap.disable_csrf', false)) {
            return false;
        }

        $userAgent = $request->userAgent();
        $bypassedAgents = config('sqlmap.bypassed_user_agents', []);

        foreach ($bypassedAgents as $agent) {
            if (str_contains($agent, '*')) {
                // Handle wildcard patterns - escape everything except *
                $pattern = preg_quote($agent, '/');
                $pattern = str_replace('\*', '.*', $pattern);
                if (preg_match("/^{$pattern}$/i", $userAgent)) {
                    return true;
                }
            } elseif (stripos($userAgent, $agent) !== false) {
                return true;
            }
        }

        return false;
    }
}
