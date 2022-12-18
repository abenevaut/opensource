<?php

namespace abenevaut\Stripe\Commands;

use abenevaut\Stripe\Actions\ListAllProductsAction;
use Illuminate\Console\Command;

class ListProductsCommand extends Command //implements ValidateCommandArgumentsInterface
{
//    use ValidateCommandArgumentsTrait;

    protected $signature = 'stripe:list:products';

    protected $description = 'List Stripe products';

    public function handle()
    {
//        try {
//            $this->validate();

        $productsList = (new ListAllProductsAction())
            ->execute()
            ->productsList;

//        $productsList->

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
