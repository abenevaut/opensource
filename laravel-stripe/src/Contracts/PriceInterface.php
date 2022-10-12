<?php

namespace abenevaut\Stripe\Contracts;

interface PriceInterface
{
    /**
     * https://stripe.com/docs/api/prices/object#price_object-id
     *
     * @return string
     */
    public function getId(): string;

    /**
     * https://stripe.com/docs/api/prices/object#price_object-active
     *
     * @return bool
     */
    public function getActive(): bool;
}