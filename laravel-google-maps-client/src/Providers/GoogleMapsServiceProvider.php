<?php

namespace abenevaut\GoogleMaps\Providers;

use abenevaut\GoogleMaps\Client\AccessToken;
use abenevaut\GoogleMaps\Client\PlacesClient;
use abenevaut\GoogleMaps\Services\GoogleMapsServiceFactory;
use abenevaut\GoogleMaps\Services\PlacesService;
use abenevaut\GoogleMaps\Services\TimezonesService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class GoogleMapsServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $driversList = $this->app->get('config')->get('google-maps')->keys();

        $this->app->singleton(GoogleMapsServiceFactory::class, function (Container $app, $driversList) {
            // @codeCoverageIgnoreStart
            return new GoogleMapsServiceFactory(
                [$app, 'resolve'],
                $driversList
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(AccessToken::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new AccessToken(
                $app->get('config')->get('google-maps.api_key')
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(PlacesService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new PlacesService(
                $app->make(AccessToken::class),
                $app->get('config')->get('google-maps.places.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(TimezonesService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new TimezonesService(
                $app->make(AccessToken::class),
                $app->get('config')->get('google-maps.timezones.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            GoogleMapsServiceFactory::class,
        ];
    }
}
