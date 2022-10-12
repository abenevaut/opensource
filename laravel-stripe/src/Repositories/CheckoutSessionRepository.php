<?php

namespace abenevaut\Stripe\Repositories;

use abenevaut\Stripe\Contracts\CouponInterface;
use abenevaut\Stripe\Contracts\CustomerInterface;
use abenevaut\Stripe\Contracts\PriceInterface;
use abenevaut\Stripe\Contracts\StripeRepositoryAbstract;
use abenevaut\Stripe\Entities\SessionEntity;
use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;

final class CheckoutSessionRepository extends StripeRepositoryAbstract
{
    /**
     * https://stripe.com/docs/api/checkout/sessions/create
     *
     * @param  CustomerInterface  $customer
     * @param  PriceInterface  $price
     * @param  array  $metadata
     * @return Session
     * @throws ApiErrorException
     */
    public function create(
        CustomerInterface $customer,
        PriceInterface $price,
        string $successUrl,
        ?CouponInterface $coupon = null,
        ?string $cancelUrl = null,
        array $metadata = []
    ) {
        $successUrl = "{$successUrl}?checkout_session_id={CHECKOUT_SESSION_ID}";

        if (is_null($cancelUrl)) {
            $cancelUrl = request()->url();
        }

        $params = [
            /*
             * https://stripe.com/docs/api/checkout/sessions/object#checkout_session_object-client_reference_id
             */
            'client_reference_id' => $customer->getClientReferenceId(),
            'payment_method_types' => [
                'card',
            ],
            'line_items' => [
                [
                    'price' => $price->getId(),
                    'quantity' => 1,
                ],
            ],
            'mode' => 'subscription',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'locale' => $customer->preferredLocale(),
            'metadata' => $metadata,
        ];

        if ($coupon) {
            /*
             * https://stripe.com/docs/api/checkout/sessions/create#create_checkout_session-discounts-coupon
             */
            $params['discounts'][]['coupon'] = $coupon->getId();
        }

        if ($customer->getId()) {
            /*
             * https://stripe.com/docs/api/checkout/sessions/create#create_checkout_session-customer
             */
            $params['customer'] = $customer->getId();
        } else {
            /*
             * https://stripe.com/docs/api/checkout/sessions/create#create_checkout_session-customer_email
             */
            $params['customer_email'] = $customer->getEmail();
        }

        return Session::create($params);
    }

    /**
     * @param  string  $sessionId
     * @return Session
     *
     * @throws ApiErrorException
     */
    public function getOne(string $sessionId): Session
    {
        return Session::retrieve($sessionId);
    }
}
