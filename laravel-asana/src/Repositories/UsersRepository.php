<?php

namespace abenevaut\Asana\Repositories;

use abenevaut\Asana\Contracts\ApiRepositoryAbstract;
use Illuminate\Support\Collection;

final class UsersRepository extends ApiRepositoryAbstract
{
    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/users"))
            ->collect();
    }

    /**
     * @param  int  $userId
     * @return Collection
     */
    public function get(int $userId): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/users/{$userId}"))
            ->collect();
    }

    /**
     * @return Collection
     */
    public function me(): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/users/me"))
            ->collect();
    }
}
