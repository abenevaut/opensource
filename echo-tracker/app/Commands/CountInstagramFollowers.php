<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountInstagramFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-instagram-followers {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of an Instagram account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->argument('username');

        try {
            $response = Http::withUserAgent(
                'Instagram 76.0.0.15.395 Android'
                . ' (24/7.0; 640dpi; 1440x2560; samsung; SM-G930F; herolte; samsungexynos8890; en_US; 138226743)'
            )
                ->get("https://i.instagram.com/api/v1/users/web_profile_info/?username={$username}")
                ->throw()
                ->json();

            $count = $response['data']['user']['edge_followed_by']['count'];
            $this->info("The number of followers of the Instagram account is {$count}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            } else {
                $this->error('An error occurred while counting the number of followers of the Instagram account.');
            }

            return self::FAILURE;
        }

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
