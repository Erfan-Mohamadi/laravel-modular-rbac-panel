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
            //areas
                //cities
            'view cities' => 'مشاهده شهر',
            'create cities' => 'ایجاد شهر',
            'modify cities' => 'ویرایش شهر',
            'delete cities' => 'حذف شهر',
                //provinces
            'view provinces' => 'مشاهده استان',
            //categories
            'view categories' => 'مشاهده دسته‌بندی',
            'create categories' => 'ایجاد دسته‌بندی',
            'modify categories' => 'ویرایش دسته‌بندی',
            'delete categories' => 'حذف دسته‌بندی',
            //brands
            'view brands' => 'مشاهده برند',
            'create brands' => 'ایجاد برند',
            'modify brands' => 'ویرایش برند',
            'delete brands' => 'حذف برند',
            //attributes
            'view attributes' => 'مشاهده خصوصیات',
            'create attributes' => 'ایجاد خصوصیات',
            'modify attributes' => 'ویرایش خصوصیات',
            'delete attributes' => 'حذف خصوصیات',
            //specialties
            'view specialties' => 'مشاهده ویژگی',
            'create specialties' => 'ایجاد ویژگی',
            'modify specialties' => 'ویرایش ویژگی',
            'delete specialties' => 'حذف ویژگی',
            //products
            'view products' => 'مشاهده محصول',
            'create products' => 'ایجاد محصول',
            'modify products' => 'ویرایش محصول',
            'delete products' => 'حذف محصول',
            //shipping
            'view shipping' => 'مشاهده حمل و نقل',
            'create shipping' => 'ایجاد حمل و نقل',
            'modify shipping' => 'ویرایش حمل و نقل',
            'delete shipping' => 'حذف حمل و نقل',
            //orders
            'view orders' => 'مشاهده سفارش',
            'create orders' => 'ایجاد سفارش',
            'modify orders' => 'ویرایش سفارش',

        ];

        foreach ($permissions as $name => $label) {
            Permission::query()->firstOrCreate(
                ['name' => $name],
                ['label' => $label, 'guard_name' => 'admin']
            );
        }
    }
}
