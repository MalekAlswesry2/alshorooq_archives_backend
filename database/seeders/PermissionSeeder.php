<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // $permissions = [
        //     'إضافة واصل',
        //     // 'تعديل واصل',
        //     // 'حذف واصل',
        //     'استلام واصل',
        //     'إضافة سوق',
        //     // 'تعديل سوق',
        //     // 'حذف سوق',
        //     'عرض المستخدمين',
        //     'إضافة ادمن',
        //     // 'تعديل مستخدم',
        //     // 'حذف مستخدم'
        //     'عرض الصلاحيات وتعديلها',
        //     'عرض الأقسام',
        //     'عرض الفروع',
        //     'عرض المصارف',
        //     'عرض المناطق',
        //     'عرض خطوط السير',
        // ];
        $permissions = [
            ['name' => 'إضافة واصل', 'key' => 'receipts_create'],
            ['name' => 'استلام واصل', 'key' => 'receipts_receive'],
            ['name' => 'إضافة سوق', 'key' => 'markets_create'],
            ['name' => 'عرض المستخدمين', 'key' => 'users_view'],
            ['name' => 'إضافة ادمن', 'key' => 'admins_create'],
            ['name' => 'عرض الصلاحيات وتعديلها', 'key' => 'permissions_view'],
            ['name' => 'عرض الأقسام', 'key' => 'departments_view'],
            ['name' => 'عرض الفروع', 'key' => 'branches_view'],
            ['name' => 'عرض المصارف', 'key' => 'banks_view'],
            ['name' => 'عرض المناطق', 'key' => 'zones_view'],
            ['name' => 'عرض خطوط السير', 'key' => 'routes_view'],
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['key' => $permission['key']],
                ['name' => $permission['name']]
            );
        }
    }
}
