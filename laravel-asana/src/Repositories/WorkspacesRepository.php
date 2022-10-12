<?php

namespace abenevaut\Asana\Repositories;

use abenevaut\Asana\Contracts\ApiRepositoryAbstract;
use Illuminate\Support\Collection;

final class WorkspacesRepository extends ApiRepositoryAbstract
{
    /**
     * @return Collection
     */
    public function all(): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/workspaces"))
            ->collect();
    }
}
