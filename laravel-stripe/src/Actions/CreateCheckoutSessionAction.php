<?php

namespace abenevaut\Stripe\Actions;

use abenevaut\Stripe\Contracts\CouponInterface;
use abenevaut\Stripe\Contracts\CustomerInterface;
use abenevaut\Stripe\Contracts\PriceInterface;
use abenevaut\Stripe\Contracts\StripeDriversEnum;
use abenevaut\Stripe\Events\CheckoutSessionCreatedEvent;
use abenevaut\Stripe\Facades\Stripe;
use Illuminate\Support\Facades\Event;
use Stripe\Checkout\Session;

final class CreateCheckoutSessionAction
{
    public Session $session;

    public function execute(
        CustomerInterface $customer,
        PriceInterface $price,
        string $successUrl,
        ?CouponInterface $coupon = null,
        ?string $cancelUrl = null,
        array $metadata = []
    ) {
        $this->session = Stripe::request(StripeDriversEnum::SESSIONS)
            ->create(
                $customer,
                $price,
                $successUrl,
                $coupon,
                $cancelUrl,
                $metadata
            );

        Event::dispatch(new CheckoutSessionCreatedEvent($this->session));
    }
}
