<?php

namespace abenevaut\Kite\App\Providers;

use abenevaut\Kite\Domain\Users\Users\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => \abenevaut\Kite\Domain\Users\Users\UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('can-login', function (?User $user = null) {
            return Route::has('login');
        });

        Gate::define('can-register', function (?User $user = null) {
            return Route::has('register');
        });

        Gate::define('can-reset-password', function (?User $user = null) {
            return Route::has('password.request');
        });
    }
}
