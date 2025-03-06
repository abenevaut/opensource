<?php

namespace abenevaut\Linkedin\Providers;

use abenevaut\Linkedin\Client\AccessToken;
use abenevaut\Linkedin\Client\LinkedinClient;
use abenevaut\Linkedin\Services\LinkedinService;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;

class LinkedinServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function register()
    {
        parent::register();

        $this->app->singleton('linkedin.http-client.authenticated', function (Container $app) {
            // @codeCoverageIgnoreStart
            $accessToken = new AccessToken(
                $app->get('config')->get('services.linkedin.api_key')
            );
            return new LinkedinClient(
                $app->get('config')->get('services.linkedin.baseUrl'),
                $accessToken,
                $app->get('config')->get('services.linkedin.debug', false),
            );
            // @codeCoverageIgnoreEnd
        });

        $this->app->singleton(LinkedinService::class, function (Container $app) {
            // @codeCoverageIgnoreStart
            return new LinkedinService($app->make('linkedin.http-client.authenticated'));
            // @codeCoverageIgnoreEnd
        });
    }

    public function provides()
    {
        return [
            'linkedin.http-client.authenticated',
            LinkedinService::class,
        ];
    }
}
