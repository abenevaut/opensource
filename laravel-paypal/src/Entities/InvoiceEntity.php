<?php

namespace abenevaut\Paypal\Entities;

use abenevaut\Paypal\Contracts\InvoiceInterface;
use Spatie\DataTransferObject\DataTransferObject;

class InvoiceEntity extends DataTransferObject implements InvoiceInterface
{
    public string $id;

    public function getId(): string
    {
        return $this->id;
    }
}