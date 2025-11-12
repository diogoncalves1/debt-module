<?php

namespace Modules\Permission\Database\seeders;

use Modules\Permission\Entities\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::factory(3)->create();
    }
}
