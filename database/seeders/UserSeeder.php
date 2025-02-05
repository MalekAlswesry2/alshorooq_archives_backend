<?php

namespace Database\Seeders;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Permission;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // إنشاء مستخدم
        $user = User::updateOrCreate(
            // التعريف الفريد للمستخدم
            [
                'name' => 'Master User',
                'email' => 'master@master.com',
                'role' => 'master',
                'phone' => '0918765432',
                'password' => bcrypt('Master321'), // كلمة المرور
            ]
        );
        
        $user = User::updateOrCreate(
            // التعريف الفريد للمستخدم
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'phone' => '0912345678',
                'password' => bcrypt('Admin321'), // كلمة المرور
            ]
        );

        # إنشاء مستخدم آخر

        // تعيين الصلاحيات للمستخدم
        $permissions = Permission::whereIn('name', ['can_view', 'can_edit'])->pluck('id');
        $user->permissions()->sync($permissions);
    }
}
