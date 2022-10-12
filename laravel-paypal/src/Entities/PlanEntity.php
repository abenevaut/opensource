<?php

namespace abenevaut\Paypal\Entities;

use abenevaut\Paypal\Contracts\PlanInterface;
use Spatie\DataTransferObject\DataTransferObject;

class PlanEntity extends DataTransferObject implements PlanInterface
{
    public string $id;

    public $status;

    public function getId(): string
    {
        return $this->id;
    }
}