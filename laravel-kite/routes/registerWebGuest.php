<?php

use abenevaut\Kite\Http\Controllers\{
    Auth\AuthenticatedSessionController,
    Auth\NewPasswordController,
    Auth\PasswordResetLinkController,
    Auth\RegisteredUserController
};
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'guest'])->group(function () {

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

});
