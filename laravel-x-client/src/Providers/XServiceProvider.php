<?php

namespace abenevaut\X\Providers;

use abenevaut\X\Client\AccessToken;
use abenevaut\X\Client\XAnonymousClient;
use abenevaut\X\Client\XClient;
use abenevaut\X\Services\XService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class XServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('x.http-client.anonymous', function (Container $app) {
            // @codeCoverageIgnoreStart
            return new XAnonymousClient(
                $app->get('config')->get('services.x.baseUrl'),
                $app->get('config')->get('services.x.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton('x.http-client.authenticated', function (Container $app) {
            // @codeCoverageIgnoreStart
            $accessToken = new AccessToken(
                $app->make('x.http-client.anonymous'),
                $app->get('config')->get('services.x.client_id'),
                $app->get('config')->get('services.x.client_secret')
            );
            return new XClient(
                $app->get('config')->get('services.x.baseUrl'),
                $accessToken,
                $app->get('config')->get('services.x.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(XService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new XService($app->make('x.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'x.http-client.anonymous',
            'x.http-client.authenticated',
            XService::class,
        ];
    }
}
