<?php

namespace abenevaut\Ohdear\Entities;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class UptimeEntityFactory extends Factory
{
    protected $model = UptimeEntity::class;

    public function modelName()
    {
        return $this->model;
    }

    public function definition(): array
    {
        $this->faker = \Faker\Factory::create();

        return [
            'datetime' => Carbon::now()->format('Y-m-d H:i:s'),
            'uptime_percentage' => $this->faker->numberBetween(1, 100),
        ];
    }
}