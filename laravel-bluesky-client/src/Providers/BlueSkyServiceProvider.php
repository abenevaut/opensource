<?php

namespace abenevaut\BlueSky\Providers;

use abenevaut\BlueSky\Client\AccessToken;
use abenevaut\BlueSky\Client\BlueSkyAnonymousClient;
use abenevaut\BlueSky\Client\BlueSkyClient;
use abenevaut\BlueSky\Services\BlueSkyService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class BlueSkyServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('bluesky.http-client.anonymous', function (Container $app) {
            // @codeCoverageIgnoreStart
            return new BlueSkyAnonymousClient(
                $app->get('config')->get('services.bluesky.baseUrl'),
                $app->get('config')->get('services.bluesky.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton('bluesky.http-client.authenticated', function (Container $app) {
            // @codeCoverageIgnoreStart
            $accessToken = new AccessToken(
                $app->make('bluesky.http-client.anonymous'),
                $app->get('config')->get('services.bluesky.identifier'),
                $app->get('config')->get('services.bluesky.password')
            );
            return new BlueSkyClient(
                $app->get('config')->get('services.bluesky.baseUrl'),
                $accessToken,
                $app->get('config')->get('services.bluesky.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(BlueSkyService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new BlueSkyService($app->make('bluesky.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'bluesky.http-client.anonymous',
            'bluesky.http-client.authenticated',
            BlueSkyService::class,
        ];
    }
}
