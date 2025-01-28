<?php

namespace App\Commands;

use abenevaut\BlueSky\Client\AccessToken;
use abenevaut\BlueSky\Client\BlueSkyAnonymousClient;
use abenevaut\BlueSky\Client\BlueSkyClient;
use abenevaut\BlueSky\Services\BlueSkyService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountBlueSkyFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-bluesky-followers {username} {password} {account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a BlueSky account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $username = $this->argument('username');
            $password = $this->argument('password');
            $account = $this->argument('account');

            $client = new BlueSkyAnonymousClient('https://bsky.social', false);
            $accessToken = new AccessToken($client, $username, $password);
            $client = new BlueSkyClient('https://bsky.social', $accessToken, false);
            $nbFollowers = (new BlueSkyService($client))->countFollowers($account);

            $this->info("The number of followers of the BlueSky account is {$nbFollowers}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            } else {
                $this->error('An error occurred while counting the number of followers of the BlueSky account.');
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
