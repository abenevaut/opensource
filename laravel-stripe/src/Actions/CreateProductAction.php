<?php

namespace abenevaut\Stripe\Actions;

use abenevaut\Stripe\Contracts\ActionInterface;
use abenevaut\Stripe\Entities\ProductEntity;
use Carbon\Carbon;
use Spatie\DataTransferObject\DataTransferObject;
use Stripe\Service\ProductService;
use Stripe\StripeClient;
use Spatie\DataTransferObject\Attributes\CastWith;
use Attribute;

class CreateProductAction extends DataTransferObject // implements ActionInterface
{
    public $product;

    public function execute(ProductEntity $product): self
    {
        $stripe = new StripeClient(config('services.stripe.secret_key'));

        $this->product = (new ProductService($stripe))
            ->create($product->toArray());

        dd($this->product);

        return $this;
    }
}