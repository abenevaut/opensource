<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
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
        $username = $this->argument('username');
        $password = $this->argument('password');
        $account = $this->argument('account');

        try {
            $response = Http::acceptJson()
                ->get('https://bsky.social/xrpc/com.atproto.identity.resolveHandle', [
                    'handle' => $username,
                ])
                ->throw()
                ->json();

            $did = $response['did'];

            $response = Http::acceptJson()
                ->post('https://bsky.social/xrpc/com.atproto.server.createSession', [
                    'identifier' => $did,
                    'password' => $password,
                ])
                ->throw()
                ->json();

            $accessToken = $response['accessJwt'];

            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}"
                ])
                ->get("https://bsky.social/xrpc/app.bsky.actor.getProfiles", [
                    'actors' => $account,
                ])
                ->throw()
                ->json();

            $nbFollowers = $response['profiles'][0]['followersCount'];

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
