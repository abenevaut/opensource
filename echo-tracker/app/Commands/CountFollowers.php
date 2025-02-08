<?php

namespace App\Commands;

use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;

class CountFollowers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:count-followers {configuration_path}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Count the number of followers of a social media accounts';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $configuration = [];
        $configurationPath = $this->argument('configuration_path');

        if (!file_exists($configurationPath) || is_dir($configurationPath)) {
            $this->error('The configuration file does not exist.');

            return self::FAILURE;
        }

        $configurationExists = file_exists($configurationPath);

        if (!$configurationExists && $this->confirm('Would you configure BlueSky account?')) {
            $username = $this->ask('What is your username?');
            $password = $this->ask('What is your password?');
            $account = $this->ask('Which account do you want to count followers?');

            $configuration['bluesky'] = [
                'username' => $username,
                'password' => $password,
                'account' => $account,
            ];
        }

        if (!$configurationExists && $this->confirm('Would you configure Discord account?')) {
            $invitationLink = $this->ask('What is the server invitation link?');

            $configuration['discord'] = [
                'invitation_link' => $invitationLink,
            ];
        }

        if (!$configurationExists && $this->confirm('Would you configure Instagram account?')) {
            $username = $this->ask('What is your username?');

            $configuration['instagram'] = [
                'username' => $username,
            ];
        }

        if (!$configurationExists && $this->confirm('Would you configure Twitch account?')) {
            $clientId = $this->ask('What is your client ID?');
            $clientSecret = $this->ask('What is your client secret?');
            $broadcaster = $this->ask('Which broadcaster do you want to count followers?');

            $configuration['twitch'] = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'broadcaster' => $broadcaster,
            ];
        }

        if (!$configurationExists && $this->confirm('Would you configure Twitter account?')) {
            $clientId = $this->ask('What is your client ID?');
            $clientSecret = $this->ask('What is your client secret?');
            $username = $this->ask('Which account do you want to count followers?');

            $configuration['twitter'] = [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'username' => $username,
            ];
        }

        if (!$configurationExists) {
            file_put_contents($configurationPath, json_encode($configuration, JSON_PRETTY_PRINT));
        }

        $configuration = json_decode(file_get_contents($configurationPath), true);

        foreach ($configuration as $socialMedia => $credentials) {
            $this->call("app:count-{$socialMedia}-followers", $credentials);
        }
    }

    /**
     * Define the command's schedule.
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
