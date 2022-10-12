<?php

namespace abenevaut\Paypal\Contracts;

/**
 * https://developer.paypal.com/docs/api/subscriptions/v1/#plans-list-response
 */
interface PlanInterface
{
    public function getId(): string;
}