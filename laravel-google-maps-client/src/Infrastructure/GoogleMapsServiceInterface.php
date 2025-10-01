<?php

namespace abenevaut\GoogleMaps\Infrastructure;

use abenevaut\Infrastructure\Client\ClientAbstract;

interface GoogleMapsServiceInterface
{
    public function getClient(): ClientAbstract;
}
