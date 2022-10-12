<?php

namespace abenevaut\Paypal\Repositories;

use abenevaut\Paypal\Contracts\PaypalApiRepositoryAbstract;
use abenevaut\Paypal\Contracts\PaypalEntitiesEnum;
use abenevaut\Paypal\Entities\PlanEntity;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

final class PlansRepository extends PaypalApiRepositoryAbstract
{
    public function all(): LengthAwarePaginator
    {
        $response = $this
            ->request()
            ->get($this->makeUrl("/v1/billing/plans"))
            ->json();

        $resources = Collection::make($response['plans'])
            ->toPaypalEntity(PaypalEntitiesEnum::PLAN);

        return new LengthAwarePaginator(
            $resources,
            $resources->count(),
            $response['total_items'],
            $response['total_pages']
        );
    }

    public function get(string $planId): PlanEntity
    {
        $plan = $this
            ->request()
            ->get($this->makeUrl("/v1/billing/plans/{$planId}"))
            ->json();

        return new PlanEntity($plan);
    }

    public function create(array $params): PlanEntity
    {
        $plan = $this
            ->request()
            ->post($this->makeUrl("/v1/billing/plans"), [
                'product_id' => $params['product_id'],
                'name' => $params['name'],
                'billing_cycles' => [
                    [
                        'frequency' => [
                            'interval_unit' => $params['interval'],
                            'interval_count' => $params['interval_count'],
                        ],
                        'tenure_type' => 'REGULAR',
                        'sequence' => 1,
                        'total_cycles' => 0,
                        'pricing_scheme' => [
                            'fixed_price' => [
                                'value' => $params['amount'],
                                'currency_code' => 'EUR',
                            ],
                        ],
                    ],
                ],
                'payment_preferences' => [
                    'payment_failure_threshold' => 1,
                ],
                'taxes' => [
                    'percentage' => 20,
                ],
            ])
            ->body();

        return new PlanEntity($plan);
    }
}
