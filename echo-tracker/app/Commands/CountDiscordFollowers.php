<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Http;
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
        $invitationLink = $this->argument('invitation_link');

        $this->info($invitationLink);

        if (strpos($invitationLink, 'https://discord.gg') === false) {
            $this->error('Invalid Discord invitation link.');

            return self::FAILURE;
        }

        $invitationId = explode('/', $invitationLink);
        $invitationId = $invitationId[3] ?? '';

        $this->info($invitationId);

        if (empty($invitationId)) {
            $this->error('Invalid Discord invitation link.');

            return self::FAILURE;
        }

        if (preg_match('/[^a-zA-Z0-9-_]/', $invitationId)) {
            $this->error('Invalid Discord invitation link.');

            return self::FAILURE;
        }

        try {
            $response = Http::get(
                "https://discord.com/api/v9/invites/{$invitationId}?with_counts=true&with_expiration=true"
            )
                ->throw()
                ->json();

            $this->info("The Discord server has {$response['approximate_member_count']} members.");
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
