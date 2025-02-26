<?php

namespace App\Commands;

use abenevaut\Twitch\Client\AccessToken;
use abenevaut\Twitch\Client\TwitchAnonymousClient;
use abenevaut\Twitch\Client\TwitchClient;
use abenevaut\Twitch\Services\TwitchService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountTwitchFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-twitch-followers {client_id} {client_secret} {broadcaster}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a Twitch broadcaster';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $clientId = $this->argument('client_id');
            $clientSecret = $this->argument('client_secret');
            $broadcaster = $this->argument('broadcaster');

            $client = new TwitchAnonymousClient('https://id.twitch.tv');
            $accessToken = new AccessToken($client, $clientId, $clientSecret);
            $client = new TwitchClient('https://api.twitch.tv/helix', $accessToken, false);
            $nbFollowers = (new TwitchService($client))->countFollowers($broadcaster);

            $this->info("The number of followers of the Twitch broadcaster is {$nbFollowers}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            } else {
                $this->error('An error occurred while counting the number of followers of the Twitch broadcaster.');
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
