<?php

use Illuminate\Support\Facades\Route;

/**
 *
 */
Route::get('/report-std-exception', function () {
    $exception = new \Exception('report-std-exception');

    report($exception);

    // Example purpose only
    return response()->json(['message' => $exception->getMessage()]);
});

/**
 *
 */
Route::get('/throw-std-exception-to-handler', function () {
    throw new \App\Exceptions\NotCaughtStandardException('throw-std-exception-to-handler', 418);
});

/**
 *
 */
Route::get('/report-scoped-exception', function () {
    $user = new \App\Models\User(['name' => 'UserName: throw-scoped-exception-to-handler']);

    $exception = new \App\Exceptions\NotCaughtScopedException('report-scoped-exception', 418);
    $exception->addScope(\App\Exceptions\Scopes\DemoScope::class);
    $exception->addScope(new \App\Exceptions\Scopes\DemoTwoScope($user));

    report($exception);

    // Example purpose only
    return response()->json(['message' => $exception->getMessage()]);
});

/**
 * Exception based on HTTPExceptionAbstract render alone his json response.
 */
Route::get('/throw-scoped-exception-to-handler', function () {
    $user = new \App\Models\User(['name' => 'UserName: throw-scoped-exception-to-handler']);

    $exception = new \App\Exceptions\HttpNotCaughtScopedException('throw-scoped-exception-to-handler', 418);
    $exception->addScope(\App\Exceptions\Scopes\DemoScope::class);
    $exception->addScope(new \App\Exceptions\Scopes\DemoTwoScope($user));

    throw $exception;
});
