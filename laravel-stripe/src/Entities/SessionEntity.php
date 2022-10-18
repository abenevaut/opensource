<?php

namespace abenevaut\Stripe\Entities;

use Stripe\Checkout\Session;

class SessionEntity
{
    public static function from(Session $session): SessionEntity
    {
        $sessionEntity = new SessionEntity();



        return $sessionEntity;
    }
}