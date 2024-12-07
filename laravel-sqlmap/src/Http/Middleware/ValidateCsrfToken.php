<?php

namespace abenevaut\SqlMap\Http\Middleware;

use Closure;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\RateLimiter;

class ValidateCsrfToken extends VerifyCsrfToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (
            $this->app->isLocal()
            && in_array($request->header('User-Agent'), config('app.rate_limiter.bypassed_user_agents', []))
        ) {
            RateLimiter::clear($request->ip());
        }

        return parent::handle($request, $next);
    }

    /**
     * Determine if the CSRF validation should be disabled for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function runningUnitTests()
    {
        return config('app.csrf.disable_validation')
            || parent::runningUnitTests();
    }
}
