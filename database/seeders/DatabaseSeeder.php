<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\SubscriptionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SubscriptionSeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(ContactSeeder::class);
    }
}
