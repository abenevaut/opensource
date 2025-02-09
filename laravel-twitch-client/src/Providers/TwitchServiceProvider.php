<?php

namespace abenevaut\Twitch\Providers;

use abenevaut\Twitch\Client\AccessToken;
use abenevaut\Twitch\Client\TwitchAnonymousClient;
use abenevaut\Twitch\Client\TwitchClient;
use abenevaut\Twitch\Services\TwitchService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class TwitchServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('twitch.http-client.anonymous', function (Container $app) {
            // @codeCoverageIgnoreStart
            return new TwitchAnonymousClient(
                $app->get('config')->get('services.twitch.oauthBaseUrl'),
                $app->get('config')->get('services.twitch.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton('twitch.http-client.authenticated', function (Container $app) {
            // @codeCoverageIgnoreStart
            $accessToken = new AccessToken(
                $app->make('twitch.http-client.anonymous'),
                $app->get('config')->get('services.twitch.client_id'),
                $app->get('config')->get('services.twitch.client_secret')
            );
            return new TwitchClient(
                $app->get('config')->get('services.twitch.baseUrl'),
                $accessToken,
                $app->get('config')->get('services.twitch.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(TwitchService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new TwitchService($app->make('twitch.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'twitch.http-client.anonymous',
            'twitch.http-client.authenticated',
            TwitchService::class,
        ];
    }
}
