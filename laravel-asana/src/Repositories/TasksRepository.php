<?php

namespace abenevaut\Asana\Repositories;

use abenevaut\Asana\Contracts\ApiRepositoryAbstract;
use Illuminate\Support\Collection;

final class TasksRepository extends ApiRepositoryAbstract
{
    /**
     * @param  array  $filters
     * @return Collection
     */
    public function all(array $filters = []): Collection
    {
        $filters = array_merge(['assignee' => '', 'project' => '', 'workspace' => ''], $filters);
        $filters = array_filter($filters);
        $queryParams = http_build_query($filters);

        return $this
            ->request()
            ->get($this->makeUrl("/tasks?{$queryParams}"))
            ->collect();
    }

    /**
     * @param  int  $taskId
     * @return Collection
     */
    public function get(int $taskId): Collection
    {
        return $this
            ->request()
            ->get($this->makeUrl("/tasks/$taskId"))
            ->collect();
    }
}
