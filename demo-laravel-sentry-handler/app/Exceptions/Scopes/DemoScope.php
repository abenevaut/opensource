<?php

namespace App\Exceptions\Scopes;

use abenevaut\SentryHandler\Contracts\ScopeAbstract;
use Closure;
use Sentry\State\Scope;

final class DemoScope extends ScopeAbstract
{
    public function handle(Scope $scope, Closure $next)
    {
        $scope->setTags([
            'demo' => 'This a demo scope message',
        ]);

        return $next($scope);
    }
}
