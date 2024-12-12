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
            ['email' => 'admin@example.com'], // التعريف الفريد للمستخدم
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => bcrypt('password'), // كلمة المرور
            ]
        );

        // تعيين الصلاحيات للمستخدم
        $permissions = Permission::whereIn('name', ['can_view', 'can_edit'])->pluck('id');
        $user->permissions()->sync($permissions);
    }
}
