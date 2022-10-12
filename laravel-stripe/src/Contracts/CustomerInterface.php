<?php

namespace abenevaut\Stripe\Contracts;

use Illuminate\Contracts\Translation\HasLocalePreference;

interface CustomerInterface extends HasLocalePreference
{
    /**
     * @return string
     */
    public function getClientReferenceId(): string;

    /**
     * @return string
     */
    public function getId(): ?string;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * Get the preferred locale of the entity.
     *
     * @return string
     */
    public function preferredLocale(): string;
}