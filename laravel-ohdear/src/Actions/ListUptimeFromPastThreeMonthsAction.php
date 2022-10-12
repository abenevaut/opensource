<?php

namespace abenevaut\Ohdear\Actions;

use abenevaut\Ohdear\Contracts\OhdearDriversEnum;
use abenevaut\Ohdear\Entities\SiteEntity;
use abenevaut\Ohdear\Facades\Ohdear;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ListUptimeFromPastThreeMonthsAction
{
    public Collection $uptimes;

    public function __construct()
    {
        $this->uptimes = collect();
    }

    public function execute(array $sitesList): self
    {
        $sitePage = 0;

        do {
            $sitePage += 1;
            /** @var \Illuminate\Pagination\LengthAwarePaginator $sites */
            $sites = Ohdear::request(OhdearDriversEnum::SITES)->all($sitePage);

            for ($i = 2; $i >= 0; $i--) {
                $currentMonth = CarbonImmutable::now()->subMonthsNoOverflow($i);
                $startOfMonth = $currentMonth->startOfMonth();
                $endOfMonth = $currentMonth->endOfMonth();

                if ($endOfMonth->isFuture() === true) {
                    $endOfMonth = Carbon::now()->subDay()->endOfDay();
                }

                if ($endOfMonth->isAfter($startOfMonth) === false) {
                    continue;
                }

                $sites
                    ->each(function (SiteEntity $site) use ($sitesList, $currentMonth, $startOfMonth, $endOfMonth) {
                        if (in_array($site->getId(), $sitesList) === false) {
                            return;
                        }

                        $uptime = Ohdear::request(OhdearDriversEnum::SITES)
                            ->getUptime(
                                $site->getId(),
                                $startOfMonth->format('YmdHis'),
                                $endOfMonth->format('YmdHis')
                            );

                        if ($uptime) {
                            $this
                                ->uptimes
                                ->push([
                                    'month' => $currentMonth->monthName,
                                    'site' => $site->getId(),
                                    'uptime_percentage' => $uptime->uptime_percentage,
                                ]);
                            unset($uptime);
                        }
                    });
            }
        } while ($sites->isNotEmpty() && $sites->hasMorePages());

        return $this;
    }
}