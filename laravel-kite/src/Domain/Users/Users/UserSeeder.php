<?php

namespace abenevaut\Kite\Domain\Users\Users;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory(1, [
            'email' => 'admin@abenevaut.dev'
        ])->create();

        User::factory(1, [
            'email' => 'customer@abenevaut.dev'
        ])->create();

        User::factory(10)->create();
    }
}
