<?php

namespace abenevaut\Stripe\Listeners;

use abenevaut\Stripe\Events\CheckoutSessionCreatedEvent;

class LogCheckoutSession
{
    public function handle(CheckoutSessionCreatedEvent $event)
    {
        // $event->session
    }
}
