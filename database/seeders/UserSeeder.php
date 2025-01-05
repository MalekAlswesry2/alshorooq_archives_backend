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
            ['email' => 'admin@gmail.com'], // التعريف الفريد للمستخدم
            [
                'name' => 'Admin User',
                'email' => 'admin@gmail.com',
                'role' => 'admin',
                'password' => bcrypt('Admin321'), // كلمة المرور
            ]
        );

        // تعيين الصلاحيات للمستخدم
        $permissions = Permission::whereIn('name', ['can_view', 'can_edit'])->pluck('id');
        $user->permissions()->sync($permissions);
    }
}
