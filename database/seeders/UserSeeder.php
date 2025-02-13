<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Permission;

class UserSeeder extends Seeder
{
    public function run()
    {
        // جلب جميع الصلاحيات
        $allPermissions = Permission::pluck('id')->toArray();

        // إنشاء مستخدم Master
        $masterUser = User::firstOrCreate(
            [
                'phone' => '0918765432',
            ],
            [
                'name' => 'Master User',
                'role' => 'master',
                'phone' => '0918765432',
                'password' => bcrypt('Master321'), // كلمة المرور
            ]
        );

        // تعيين جميع الصلاحيات للمستخدم Master
        $masterUser->permissions()->sync($allPermissions);

        // إنشاء مستخدم Admin
        $adminUser = User::firstOrCreate(
            [
                'phone' => '0912345678',
            ],
            [
                'name' => 'Admin User',
                'role' => 'admin',
                'phone' => '0912345678',
                'password' => bcrypt('Admin321'), // كلمة المرور
            ]
        );

        // تعيين جميع الصلاحيات للمستخدم Admin
        $adminUser->permissions()->sync($allPermissions);
    }
}
