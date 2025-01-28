<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CountLinkedinFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-linkedin-followers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a LinkedIn account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('This command is not implemented yet.');

        return self::SUCCESS;
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
