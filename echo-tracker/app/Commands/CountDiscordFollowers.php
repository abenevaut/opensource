<?php

namespace App\Commands;

use abenevaut\Discord\Client\DiscordAnonymousClient;
use abenevaut\Discord\Services\DiscordService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Console\Output\OutputInterface;

class CountDiscordFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-discord-followers {invitation_link}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a Discord server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $invitationLink = $this->argument('invitation_link');

            $client = new DiscordAnonymousClient('https://discord.com/api');
            $nbFollowers = (new DiscordService($client))->countFollowers($invitationLink);

            $this->info("The number of followers of the Discord account is {$nbFollowers}.");
        } catch (\Exception $exception) {
            if ($this->verbosity === OutputInterface::VERBOSITY_DEBUG) {
                $this->error($exception->getMessage());
            } else {
                $this->error('An error occurred while counting the number of followers of the Discord server.');
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
