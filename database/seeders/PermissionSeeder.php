<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        $permissions = [
            'إضافة واصل',
            // 'تعديل واصل',
            // 'حذف واصل',
            'استلام واصل',
            'إضافة سوق',
            // 'تعديل سوق',
            // 'حذف سوق',
            'عرض المستخدمين',
            'إضافة ادمن',
            // 'تعديل مستخدم',
            // 'حذف مستخدم'
            'عرض الصلاحيات وتعديلها',
            'عرض الأقسام',
            'عرض الفروع',
            'عرض المصارف',
            'عرض المناطق',
            'عرض خطوط السير',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
    }
}
