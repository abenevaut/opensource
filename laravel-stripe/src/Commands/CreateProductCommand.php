<?php

namespace abenevaut\Stripe\Commands;

use abenevaut\Stripe\Actions\CreateProductAction;
use abenevaut\Stripe\Entities\ProductEntity;
use Illuminate\Console\Command;

class CreateProductCommand extends Command //implements ValidateCommandArgumentsInterface
{
//    use ValidateCommandArgumentsTrait;

    protected $signature = 'stripe:create:product
        {name : Product name}';

    protected $description = 'Create Stripe product';

    public function handle()
    {
//        try {
//            $this->validate();
        $product = (new CreateProductAction())
            ->execute(ProductEntity::make($this->arguments()))
            ->product;

        return self::SUCCESS;
//        } catch (ValidateCommandArgumentsException $exception) {
//            return $this->displayErrors();
//        }
    }

    protected function rules(): array
    {
        return [
        ];
    }
}
