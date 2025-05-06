<?php

namespace App\Domain\Users\Users;

use abenevaut\Kite\Domain\Users\Users\User as Authenticatable;
use ApiPlatform\Metadata\ApiResource;
use Laravel\Passport\HasApiTokens;

#[ApiResource(
    middleware: 'auth_m2m_client',
)]
class User extends Authenticatable
{
    use HasApiTokens;
}
