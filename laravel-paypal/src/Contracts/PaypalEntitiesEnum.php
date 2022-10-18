<?php

namespace abenevaut\Paypal\Contracts;

enum PaypalEntitiesEnum: string
{
    case IDENTITY = 'Identity';
    case INVOICE = 'Invoice';
    case PLAN = 'Plan';
}
