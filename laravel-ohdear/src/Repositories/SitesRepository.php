<?php

namespace abenevaut\Ohdear\Repositories;

use abenevaut\Ohdear\Contracts\ApiRepositoryAbstract;
use abenevaut\Ohdear\Entities\SiteEntity;
use abenevaut\Ohdear\Entities\UptimeEntity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class SitesRepository extends ApiRepositoryAbstract
{
    public function all(int $page = 1): LengthAwarePaginator
    {
        $params = http_build_query([
            'page[number]' => $page,
        ]);

        $response = $this
            ->request()
            ->get($this->makeUrl("/sites?{$params}"))
            ->json();

        $resources = Collection::make($response['data'])
            ->toOhdearEntity(SiteEntity::class);

        return new LengthAwarePaginator(
            $resources,
            $response['meta']['total'],
            $response['meta']['per_page'],
            $response['meta']['current_page'],
            [
                'path' => $this->makeUrl("/sites"),
                'pageName' => 'page[number]'
            ]
        );
    }

    public function getUptime(int $siteId, string $startedAt, string $endedAt, string $split = 'month'): ?UptimeEntity
    {
        $params = http_build_query([
            'filter[started_at]' => $startedAt,
            'filter[ended_at]' => $endedAt,
            'split' => $split,
        ]);

        $resource = $this
            ->request()
            ->get($this->makeUrl("/sites/{$siteId}/uptime?{$params}"))
            ->json();

        if (count($resource)) {
            return new UptimeEntity($resource[0]);
        }

        return null;
    }
}
