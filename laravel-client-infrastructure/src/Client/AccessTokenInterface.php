<?php

namespace abenevaut\Infrastructure\Client;

interface AccessTokenInterface
{
    public function getAccessToken(): string;
}
