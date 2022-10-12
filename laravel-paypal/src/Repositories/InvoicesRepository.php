<?php

namespace abenevaut\Paypal\Repositories;

use abenevaut\Paypal\Contracts\PaypalApiRepositoryAbstract;
use abenevaut\Paypal\Contracts\PaypalEntitiesEnum;
use abenevaut\Paypal\Contracts\CustomerInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class InvoicesRepository extends PaypalApiRepositoryAbstract
{
    public function all(CustomerInterface $customer): LengthAwarePaginator
    {
        $response = $this
            ->request()
            ->post($this->makeUrl("/v2/invoicing/search-invoices"), [
                'recipient_email' => $customer->getEmail(),
            ])
            ->json();

        $resources = Collection::make($response['items'])
            ->toPaypalEntity(PaypalEntitiesEnum::INVOICE);

        return new LengthAwarePaginator(
            $resources,
            $resources->count(),
            $response['total_items'],
            $response['total_pages']
        );
    }
}
