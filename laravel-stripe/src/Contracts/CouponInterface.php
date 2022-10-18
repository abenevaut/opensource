<?php

namespace abenevaut\Stripe\Contracts;

use Illuminate\Contracts\Translation\HasLocalePreference;

interface CouponInterface extends HasLocalePreference
{
    /**
     * https://stripe.com/docs/api/coupons/object#coupon_object-id
     *
     * @return string
     */
    public function getId(): string;
}