<?php

namespace abenevaut\Discord\Providers;

use abenevaut\Discord\Client\DiscordAnonymousClient;
use abenevaut\Discord\Services\DiscordService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class DiscordServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('discord.http-client.anonymous', function (Container $app) {
            // @codeCoverageIgnoreStart
            return new DiscordAnonymousClient(
                $app->get('config')->get('services.discord.baseUrl', 'https://discord.com/api'),
                $app->get('config')->get('services.discord.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(DiscordService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new DiscordService($app->make('discord.http-client.anonymous'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'discord.http-client.anonymous',
            DiscordService::class,
        ];
    }
}
