<?php

namespace abenevaut\Asana\Repositories;

use abenevaut\Asana\Contracts\ApiRepositoryAbstract;
use Illuminate\Support\Collection;

final class ProjectsRepository extends ApiRepositoryAbstract
{
    /**
     * @param  bool  $archived
     * @param  array  $optFields
     * @return Collection
     */
    public function all(bool $archived = false, array $optFields = []): Collection
    {
        $archived = $archived ? 'true' : 'false';
        $optFields = implode(',', $optFields);
        $optFields = $optFields ? "&opt_fields={$optFields}" : "";

        return $this
            ->request()
            ->get($this->makeUrl("/projects?archived={$archived}{$optFields}"))
            ->collect();
    }

    /**
     * @param $projectId
     * @return Collection
     */
    public function get($projectId): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/projects/{$projectId}"))
            ->collect();
    }
}
