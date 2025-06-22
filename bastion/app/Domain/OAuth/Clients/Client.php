<?php

namespace App\Domain\OAuth\Clients;

use Illuminate\Support\Str;
use Laravel\Passport\Client as BaseClient;

class Client extends BaseClient
{
    public function skipsAuthorization(): bool
    {
        return $this->firstParty()
            || Str::contains($this->redirect, '.abenevaut.com/callback')
            || Str::contains($this->redirect, '.abenevaut.local/callback');
    }
}
