<?php

namespace abenevaut\GoogleMaps\Providers;

use abenevaut\GoogleMaps\Client\AccessToken;
use abenevaut\GoogleMaps\Client\GoogleMapsClient;
use abenevaut\GoogleMaps\Services\GoogleMapsService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class GoogleMapsServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton(GoogleMapsClient::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            $accessToken = new AccessToken(
                $app->get('config')->get('services.googlemaps.api_key')
            );
            return new GoogleMapsClient(
                $app->get('config')->get('services.googlemaps.baseUrl'),
                $accessToken,
                $app->get('config')->get('services.googlemaps.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->alias(GoogleMapsClient::class, 'googlemaps.http-client.authenticated');

        $this->app->singleton(GoogleMapsService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new GoogleMapsService($app->make('googlemaps.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'googlemaps.http-client.authenticated',
            GoogleMapsService::class,
        ];
    }
}
