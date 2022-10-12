<?php

namespace abenevaut\Paypal\Contracts;

use Illuminate\Contracts\Translation\HasLocalePreference;

interface CustomerInterface extends HasLocalePreference
{
    public function getEmail(): string;

    /**
     * Get the preferred locale of the entity.
     */
    public function preferredLocale(): string;
}
