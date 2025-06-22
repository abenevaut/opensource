<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\DashboardController::class, 'index'])
        ->middleware(['throttle:login'])
        ->name('home');

    Route::as('profile.')
        ->prefix('me')
        ->group(function () {
            Route::prefix('profile')
                ->group(function () {
                    Route::get('/', [\App\Http\Controllers\ProfileController::class, 'edit'])->name('edit');
                    Route::patch('/', [\App\Http\Controllers\ProfileController::class, 'update'])->name('update');
                    Route::delete('/', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('destroy');
                });
        });
});
