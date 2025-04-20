<?php

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Branch;
use App\Models\Department;

class AssignAllBranchesAndDepartmentsToMasterSeeder extends Seeder
{
    public function run()
    {
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
