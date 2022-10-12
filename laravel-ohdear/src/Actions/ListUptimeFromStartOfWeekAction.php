<?php

namespace abenevaut\Ohdear\Actions;

use abenevaut\Ohdear\Contracts\OhdearDriversEnum;
use abenevaut\Ohdear\Entities\SiteEntity;
use abenevaut\Ohdear\Facades\Ohdear;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ListUptimeFromStartOfWeekAction
{
    public Collection $uptimes;

    public function __construct()
    {
        $this->uptimes = collect();
    }

    public function execute(array $sitesList): self
    {
        $sitePage = 0;
        $startOfWeek = Carbon::now()->startOfWeek();
        $now = Carbon::now()->subHour();

        do {
            $sitePage += 1;
            /** @var \Illuminate\Pagination\LengthAwarePaginator $sites */
            $sites = Ohdear::request(OhdearDriversEnum::SITES)->all($sitePage);

            $sites
                ->each(function (SiteEntity $site) use ($sitesList, $now, $startOfWeek) {
                    if (in_array($site->getId(), $sitesList) === false) {
                        return;
                    }

                    $uptime = Ohdear::request(OhdearDriversEnum::SITES)
                        ->getUptime(
                            $site->getId(),
                            $startOfWeek->format('YmdHis'),
                            $now->format('YmdHis')
                        );

                    if ($uptime) {
                        $this->uptimes->push($uptime->uptime_percentage);
                        unset($uptime);
                    }
                });
        } while ($sites->isNotEmpty() && $sites->hasMorePages());

        return $this;
    }
}