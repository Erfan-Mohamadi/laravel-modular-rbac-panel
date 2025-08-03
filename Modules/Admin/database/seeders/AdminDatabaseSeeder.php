<?php

namespace Modules\Admin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Admin\Models\Admin;
use Spatie\Permission\Models\Permission;

class AdminDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create the super admin
        $superAdmin = Admin::factory()->create([
            'name' => 'admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'mobile' => '0999999999',
        ]);

        // Give super admin all permissions
        $permissions = Permission::all()->pluck('name'); // make sure permissions are seeded first
        $superAdmin->givePermissionTo($permissions);
    }
}
