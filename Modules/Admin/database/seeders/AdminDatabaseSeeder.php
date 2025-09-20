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
            'role_id' => 1,
        ]);

        $permissions = Permission::all()->pluck('name');
        $superAdmin->givePermissionTo($permissions);
    }
}
