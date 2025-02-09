<?php

namespace App\Commands;

use abenevaut\Instagram\Client\InstagramAnonymousClient;
use abenevaut\Instagram\Services\InstagramService;
use Illuminate\Console\Scheduling\Schedule;
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
        try {
            $username = $this->argument('username');

            $client = new InstagramAnonymousClient('https://i.instagram.com/api/v1');
            $nbFollowers = (new InstagramService($client))->countFollowers($username);

            $this->info("The number of followers of the Instagram account is {$nbFollowers}.");
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
