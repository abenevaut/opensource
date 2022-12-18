<?php

namespace abenevaut\Stripe\Actions;

use abenevaut\Stripe\Contracts\ActionInterface;
use Stripe\Service\ProductService;
use Stripe\StripeClient;

class ListAllProductsAction implements ActionInterface
{
    public $productsList;

    public function execute(): self
    {
        $stripe = new StripeClient(config('services.stripe.secret_key'));

        $productsList = (new ProductService($stripe))
            ->all();

        dd($productsList);

        return $this;
    }
}