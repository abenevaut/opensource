<?php

namespace App\Exceptions\Scopes;

use abenevaut\SentryHandler\Contracts\ScopeAbstract;
use App\Models\User;
use Closure;
use Sentry\State\Scope;

final class DemoTwoScope extends ScopeAbstract
{
    public function __construct(private User $user) {}

    public function handle(Scope $scope, Closure $next)
    {
        $scope->setUser([
            'name' => $this->user->name,
        ]);

        return $next($scope);
    }
}
