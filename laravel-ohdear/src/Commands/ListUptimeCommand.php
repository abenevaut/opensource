<?php

namespace abenevaut\Ohdear\Commands;

use abenevaut\Ohdear\Actions\ListUptimeFromPastThreeMonthsAction;
use abenevaut\Ohdear\Actions\ListUptimeFromStartOfWeekAction;
use abenevaut\Ohdear\Contracts\ValidateCommandArgumentsInterface;
use abenevaut\Ohdear\Contracts\ValidateCommandArgumentsTrait;
use abenevaut\Ohdear\Exceptions\ValidateCommandArgumentsException;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use function Termwind\{render};

class ListUptimeCommand extends Command implements ValidateCommandArgumentsInterface
{
    use ValidateCommandArgumentsTrait;

    protected $signature = 'ohdear:list:uptime
        {from : Display uptime from `start_of_week` or `past_three_months`}
        {sites* : Specify sites list, separated by space}';

    protected $description = 'List OhDear uptime by site';

    public function handle()
    {
        try {
            $this->validate();

            switch ($this->argument('from')) {
                case 'start_of_week':
                    $uptimeAvg = (new ListUptimeFromStartOfWeekAction())
                        ->execute($this->argument('sites'))
                        ->uptimes
                        ->avg();

                    $uptimeAvg = round($uptimeAvg, 2);

                    $this->info("Uptime from start of week: {$uptimeAvg}%");
                    break;
                case 'past_three_months':
                    $action = (new ListUptimeFromPastThreeMonthsAction())
                        ->execute($this->argument('sites'));

                    $this
                        ->table(
                            [
                                'Month',
                                'Uptime (%)',
                            ],
                            $action
                                ->uptimes
                                ->map(function ($uptime) {
                                    $avgUptime = round(collect($uptime['uptime_percentage'])->avg(), 2);

                                    return [$uptime['month'], $avgUptime];
                                })
                                ->toArray()
                        );

                    $this->newLine();

                    $action
                        ->uptimes
                        ->groupBy('month')
                        ->each(function (Collection $data) {
                            $this
                                ->table(
                                    [
                                        'Month',
                                        'Site',
                                        'Uptime (%)',
                                    ],
                                    $data
                                        ->sortBy('site')
                                        ->values()
                                        ->toArray()
                                );
                        });

                    break;
            }

            return self::SUCCESS;
        } catch (ValidateCommandArgumentsException $exception) {
            return $this->displayErrors();
        }
    }

    protected function rules(): array
    {
        return [
            'from' => 'required|in:start_of_week,past_three_months',
            'sites' => 'required',
            'sites.*' => 'distinct|numeric|min:1',
        ];
    }
}
