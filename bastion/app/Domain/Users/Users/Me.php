<?php

namespace App\Domain\Users\Users;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use App\Domain\Users\Users\User as Authenticatable;
use App\Domain\Users\Users\Scopes\RequestUserIdOnlyScope;

#[ApiResource(
    middleware: 'auth:api',
    operations: [
        new Get(
            uriTemplate: '/me',
            requirements: [],
        ),
    ],
)]
class Me extends Authenticatable
{
    protected static function booted()
    {
        static::addGlobalScope(RequestUserIdOnlyScope::class);
    }
}
