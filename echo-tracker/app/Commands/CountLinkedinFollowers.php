<?php

namespace App\Commands;

use abenevaut\Linkedin\Client\AccessToken;
use abenevaut\Linkedin\Client\LinkedinClient;
use abenevaut\Linkedin\Services\LinkedinService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountLinkedinFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-linkedin-followers {apiKey} {companyId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a Linkedin account';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $companyId = $this->argument('companyId');
            $apiKey = $this->argument('apiKey');

            $accessToken = new AccessToken($apiKey);
            $client = new LinkedinClient('https://api.linkedin.com/v2', $accessToken);
            $nbFollowers = (new LinkedinService($client))->countCompanyFollowers($companyId);

            $this->info("The number of followers of the Linkedin account is {$nbFollowers}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            } else {
                $this->error('An error occurred while counting the number of followers of the Linkedin account.');
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
