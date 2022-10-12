<?php

namespace abenevaut\Paypal\Repositories;

use abenevaut\Paypal\Contracts\PaypalApiRepositoryAbstract;
use Illuminate\Support\Collection;

final class IdentitiesRepository extends PaypalApiRepositoryAbstract
{
    public function get(): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/v1/identity/oauth2/userinfo?schema=paypalv1.1"))
            ->collect();
    }
}
