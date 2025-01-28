<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
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
        $clientId = $this->argument('client_id');
        $clientSecret = $this->argument('client_secret');
        $broadcaster = $this->argument('broadcaster');

        try {
            $response = Http::withHeader('Accept-Language', 'en_US')
                ->acceptJson()
                ->post('https://id.twitch.tv/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                ])
                ->throw()
                ->json();

            $accessToken = $response['access_token'];

            $response = Http::withHeaders([
                'Client-ID' => $clientId,
                'Authorization' => "Bearer {$accessToken}"
            ])
                ->get("https://api.twitch.tv/helix/users?login={$broadcaster}")
                ->throw()
                ->json();

            $broadcasterId = $response['data'][0]['id'];

            $response = Http::withHeaders([
                'Client-ID' => $clientId,
                'Authorization' => "Bearer {$accessToken}"
            ])
                ->get("https://api.twitch.tv/helix/channels/followers/?broadcaster_id={$broadcasterId}")
                ->throw()
                ->json();

            $this->info("The number of followers of the Twitch broadcaster is {$response['total']}.");
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
