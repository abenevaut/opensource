<?php

use Illuminate\Support\Facades\Route;

Route::get('/health-check', [
    'uses' => 'HealthCheckController@index',
    'as' => 'health-check',
    'middleware' => 'throttle',
]);
