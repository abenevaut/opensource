<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountTwitterFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-twitter-followers {client_id} {client_secret} {account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a Twitter account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->warn('This command is not implemented yet.');

        return self::SUCCESS;

        $clientId = $this->argument('client_id');
        $clientSecret = $this->argument('client_secret');
        $account = $this->argument('account');

        try {
            $basicToken = base64_encode("{$clientId}:{$clientSecret}");
            $response = Http::acceptJson()
                ->asForm()
                ->withHeaders([
                    'Authorization' => "Basic {$basicToken}",
                ])
                ->post('https://api.x.com/2/oauth2/token', [
                    'grant_type' => 'client_credentials',
                    'client_id' => $clientId,
                    'client_secret' => $clientSecret,
                    'client_type' => 'third_party_app',
                    'scope' => 'users.read tweet.read',
                ])
                ->json();

            $accessToken = $response['access_token'];

            dump($accessToken);

            $response = Http::acceptJson()
                ->withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                ])
                ->get("https://api.x.com/2/users/by/username/{$account}", [
                    'user.fields' => 'public_metrics'
                ])
                ->throw()
                ->json();

            $count = $response['data']['public_metrics']['followers_count'];
            $this->info("The number of followers of the Twitter account is {$count}.");
        } catch (\Exception $exception) {
//            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
//            }
//            else {
//                $this->error('An error occurred while counting the number of followers of the Twitter account.');
//            }

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
