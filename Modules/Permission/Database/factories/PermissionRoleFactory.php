<?php

namespace Database\Factories;

use Modules\Permission\Entities\Role;
use Modules\Permission\Entities\Permissions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PermissionRole>
 */
class PermissionRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::pluck('id')->random(),
            'permission_id' => Permissions::pluck('id')->random()
        ];
    }
}
