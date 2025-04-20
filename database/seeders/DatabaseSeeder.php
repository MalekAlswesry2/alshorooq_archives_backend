<?php

namespace Database\Seeders;

use AssignAllBranchesAndDepartmentsToMasterSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use PhpParser\Node\Expr\Assign;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            PermissionSeeder::class,
            // AssignAllBranchesAndDepartmentsToMasterSeeder::class,
            UserSeeder::class,
        ]);
    }
}
