<?php

use abenevaut\Kite\Http\Controllers\{
    Auth\AuthenticatedSessionController,
    Auth\NewPasswordController,
    Auth\PasswordResetLinkController,
    Auth\RegisteredUserController
};
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () {

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(['throttle:login'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

});
