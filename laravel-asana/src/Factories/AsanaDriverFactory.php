<?php

namespace abenevaut\Asana\Factories;

use abenevaut\Asana\Contracts\ApiRepositoryAbstract;
use abenevaut\Asana\Contracts\AsanaDriversEnum;
use Illuminate\Foundation\Application;

class AsanaDriverFactory
{
    /**
     * @param  Application  $app
     */
    public function __construct(private Application $app)
    {
    }

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public function request(AsanaDriversEnum $driver): ApiRepositoryAbstract
    {
        return $this
            ->app
            ->make('\\abenevaut\\Asana\\Repositories\\' . $driver->value . 'Repository', [
                'access_token' => $this->app['config']->get('service.asana.access_token'),
                'debug' => $this->app->hasDebugModeEnabled(),
            ]);
    }
}
