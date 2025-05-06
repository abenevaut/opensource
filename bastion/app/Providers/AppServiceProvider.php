<?php

namespace App\Providers;

use abenevaut\Kite\Http\Exceptions\TooManyRequestsHttpException;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $tokenExpirationDelay = 60;

        \URL::forceScheme('https');

        if ($this->app->environment('production')) {
            $tokenExpirationDelay = 15*60;
            Passport::hashClientSecrets();
        }

        $tokenExpiresAt = now()->addSeconds($tokenExpirationDelay);
        Passport::tokensExpireIn($tokenExpiresAt);
        Passport::refreshTokensExpireIn($tokenExpiresAt->clone()->addMinutes(15));
        Passport::personalAccessTokensExpireIn(now()->addMonth());
        Passport::useClientModel(\App\Domain\OAuth\Clients\Client::class);
        Passport::enablePasswordGrant();

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    throw new TooManyRequestsHttpException($request, $headers, 'API rate limit exceeded');
                });
        });
    }
}
