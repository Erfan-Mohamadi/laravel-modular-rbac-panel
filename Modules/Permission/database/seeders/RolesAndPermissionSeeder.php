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
            'access_dashboard' => 'مشاهده آمارهای داشبورد',
            //customer
            'view customers' => 'مشاهده کاربر',
            'create customers' => 'ایجاد کاربر',
            'modify customers' => 'ویرایش کاربر',
            'delete customers' => 'حذف کاربر',
            ];

        foreach ($permissions as $name => $label) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'guard_name' => 'admin']
            );
        }
    }
}
