<?php

namespace abenevaut\Stripe\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Stripe\Checkout\Session;

class CheckoutSessionCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  Session  $session
     */
    public function __construct(public readonly Session $session)
    {
    }
}
