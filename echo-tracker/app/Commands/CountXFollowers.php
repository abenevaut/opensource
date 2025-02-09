<?php

namespace App\Commands;

use abenevaut\X\Client\AccessToken;
use abenevaut\X\Client\XAnonymousClient;
use abenevaut\X\Client\XClient;
use abenevaut\X\Services\XService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountXFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-x-followers {client_id} {client_secret} {account}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a X account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $this->warn('This command is not implemented yet.');
            return self::SUCCESS;

            $clientId = $this->argument('client_id');
            $clientSecret = $this->argument('client_secret');
            $account = $this->argument('account');

            $client = new XAnonymousClient('https://api.x.com/2');
            $accessToken = new AccessToken($client, $clientId, $clientSecret);
            $client = new XClient('https://api.x.com/2', $accessToken, false);
            $nbFollowers = (new XService($client))->countFollowers($account);

            $this->info("The number of followers of the X account is {$nbFollowers}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            }
            else {
                $this->error('An error occurred while counting the number of followers of the X account.');
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
