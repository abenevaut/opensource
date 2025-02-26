<?php

namespace abenevaut\Instagram\Providers;

use abenevaut\Instagram\Client\InstagramAnonymousClient;
use abenevaut\Instagram\Services\InstagramService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class InstagramServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('instagram.http-client.anonymous', function (Container $app) {
            // @codeCoverageIgnoreStart
            return new InstagramAnonymousClient(
                $app->get('config')->get('services.instagram.baseUrl'),
                $app->get('config')->get('services.instagram.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(InstagramService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new InstagramService($app->make('instagram.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'instagram.http-client.anonymous',
            InstagramService::class,
        ];
    }
}
