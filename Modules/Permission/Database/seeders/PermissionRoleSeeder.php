<?php

namespace Modules\Permission\Database\seeders;

use Modules\Permission\Entities\PermissionRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PermissionRole::factory(3)->create();
    }
}
