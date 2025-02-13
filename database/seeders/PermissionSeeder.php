<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // $permissions = 
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
            ['name' => 'عرض الصلاحيات وتعديلها', 'key' => 'permissions_view'],
            ['name' => 'عرض الواصلات', 'key' => 'receipts_view'],
            ['name' => 'إضافة واصل', 'key' => 'receipts_create'],
            ['name' => 'استلام واصل', 'key' => 'receipts_receive'],
            ['name' => 'عرض العملاء', 'key' => 'markets_view'],
            ['name' => 'إضافة عميل', 'key' => 'markets_create'],
            ['name' => 'تعديل العميل', 'key' => 'markets_update'],
            ['name' => 'عرض المستخدمين', 'key' => 'users_view'],
            ['name' => 'إضافة ادمن', 'key' => 'admins_create'],
            ['name' => 'عرض الأقسام', 'key' => 'departments_view'],
            ['name' => 'عرض الفروع', 'key' => 'branches_view'],
            ['name' => 'عرض المصارف', 'key' => 'banks_view'],
            ['name' => 'عرض خطوط السير', 'key' => 'zones_view'],
            ['name' => 'عرض المناطق', 'key' => 'areas_view'],
            ['name' => 'عرض سجل الانشطة', 'key' => 'logs_view'],
            ['name' => 'عرض الواصلات', 'key' => 'logs_view'],
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['key' => $permission['key']],
                ['name' => $permission['name']]
            );
        }
    }
}
