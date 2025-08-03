<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Example: Add any seeders you want to run globally
        // $this->call(UserSeeder::class);

        // For module seeding
        \Artisan::call('module:seed', ['module' => 'Permission']);
        \Artisan::call('module:seed', ['module' => 'Admin']);
    }
}
