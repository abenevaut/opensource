<?php

namespace abenevaut\Paypal\Contracts;

enum PaypalDriversEnum: string
{
    case IDENTITIES = 'Identities';
    case INVOICES = 'Invoices';
    case PLANS = 'Plans';
}
