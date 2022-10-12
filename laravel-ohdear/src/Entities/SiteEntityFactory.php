<?php

namespace abenevaut\Ohdear\Entities;

use Illuminate\Database\Eloquent\Factories\Factory;

class SiteEntityFactory extends Factory
{
    protected $model = SiteEntity::class;

    public function modelName()
    {
        return $this->model;
    }

    public function definition(): array
    {
        $this->faker = \Faker\Factory::create();

        return [
            'id' => $this->faker->randomNumber(),
            'url' => $this->faker->url(),
            'sort_url' => $this->faker->domainName().$this->faker->tld(),
            'label' => $this->faker->userName(),
            'team_id' => $this->faker->randomDigit(),
            'group_name' => $this->faker->userName(),
            'latest_run_date' => $this->faker->dateTime()->format('Y-m-d H:i:s'),
            'summarized_check_result' => $this->faker->randomElement(['succeeded', 'failed']),
            'created_at' => $this->faker->dateTime()->format('Ymd H:i:s'),
            'updated_at' => $this->faker->dateTime()->format('Ymd H:i:s'),
            'checks' => $this->faker->randomElements([
                [
                    "id" => 1,
                    "type" => "uptime",
                    "label" => "Uptime",
                    "enabled" => true,
                    "latest_run_ended_at" => "2019-09-16 07:29:02",
                    "latest_run_result" => "succeeded",
                    "summary" => "up"
                ],
                [
                    "id" => 2,
                    "type" => "broken_links",
                    "label" => "Broken links",
                    "enabled" => true,
                    "latest_run_ended_at" => "2019-09-16 07:29:05",
                    "latest_run_result" => "failed",
                    "summary" => "1 found"
                ],
                [
                    "id" => 3,
                    "type" => "mixed_content",
                    "label" => "Mixed content",
                    "enabled" => true,
                    "latest_run_ended_at" => "2019-09-16 07:29:05",
                    "latest_run_result" => "succeeded",
                    "Summary" => ""
                ],
                [
                    "id" => 4,
                    "type" => "certificate_health",
                    "label" => "Certificate health",
                    "enabled" => true,
                    "latest_run_ended_at" => "2019-09-16 07:29:02",
                    "latest_run_result" => "failed",
                    "Summary" => "Problems detected"
                ],
                [
                    "id" => 5,
                    "type" => "certificate_transparency",
                    "label" => "Certificate transparency",
                    "enabled" => true,
                    "latest_run_ended_at" => null,
                    "latest_run_result" => null,
                    "summary" => null
                ]
            ]),
        ];
    }
}