<?php

namespace Modules\Permission\Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles with labels
        $roles = [
            'super_admin' => 'مدیر ارشد',
        ];

        foreach ($roles as $name => $label) {
            Role::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'guard_name' => 'admin']
            );
        }

        $permissions = [
            'access_dashboard' => 'دسترسی به داشبورد',
            //customer
            'view customers' => 'دیدن کاربر',
            'create customers' => 'ساختن کاربر',
            'modify customers' => 'ویرایش کاربر',
            'delete customers' => 'پاک کردن کاربر',
            ];

        foreach ($permissions as $name => $label) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'guard_name' => 'admin']
            );
        }
    }
}
