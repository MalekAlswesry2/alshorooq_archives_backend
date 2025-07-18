<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Department;
use Illuminate\Database\Seeder;
use App\Models\Permission;
use App\Models\User;

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
            // ['name' => 'عرض المواعيد', 'key' => 'appointments_view'],
            // ['name' => 'عرض الأرشيف', 'key' => 'archives_view'],
        ];
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['key' => $permission['key']],
                ['name' => $permission['name']]
            );
        }

                // جلب المستخدم الذي دوره master
                $master = User::where('role', 'master')->first();

                if (!$master) {
                    $this->command->warn('لم يتم العثور على مستخدم ماستر');
                    return;
                }

                // جلب جميع الفروع والأقسام
                $allBranches = Branch::pluck('id')->toArray();
                $allDepartments = Department::pluck('id')->toArray();

                // ربطهم بالمستخدم
                $master->branches()->sync($allBranches);
                $master->departments()->sync($allDepartments);

                $this->command->info('تم ربط جميع الفروع والأقسام بالماستر بنجاح.');
            }

}
