<?php

namespace abenevaut\HarvestWheat;

use Illuminate\Database\Connection;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Collection;

class TestingDatabaseService
{
    public Collection $queries;

    public function __construct()
    {
        $this->setUp();
    }

    public function setUp(): void
    {
        $this->queries = new Collection();
    }

    public function getQueries(): Collection
    {
        return $this->queries;
    }

    public function pushQuery(QueryExecuted $query): void
    {
        $this->queries->push([
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'flag' => false,
        ]);
    }

    public function flagQuery(QueryExecuted $query): void
    {
        $this->queries->push([
            'sql' => $query->sql,
            'bindings' => $query->bindings,
            'time' => $query->time,
            'flag' => true,
        ]);
    }
}
