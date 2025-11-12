<?php

namespace Modules\Permission\Database\seeders;

use Modules\Permission\Entities\Permissions;
use Illuminate\Database\Seeder;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permissions::factory(3)->create();
    }
}
