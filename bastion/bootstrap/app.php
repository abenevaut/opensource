<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('client_credentials')
                ->prefix('api')
                ->name('api.')
                ->group(base_path('routes/client_credentials.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->alias([
                'auth_m2m_client' => \Laravel\Passport\Http\Middleware\CheckClientCredentials::class,
            ])
            ->throttleApi()
            ->web(
                append: [
                    \App\Http\Middleware\HandleInertiaRequests::class,
                    \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
                ],
            )
            ->replaceInGroup(
                'web',
                \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
                \App\Http\Middleware\ValidateCsrfToken::class,
            )
            ->appendToGroup(
                'client_credentials',
                [
//                    \App\Http\Middleware\IdentifyClientRequestMiddleware::class,
                    'auth_m2m_client',
                    \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
                    \Illuminate\Routing\Middleware\SubstituteBindings::class,
                ]
            );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
