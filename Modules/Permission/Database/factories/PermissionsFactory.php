<?php

namespace Database\Factories;

use Modules\Permission\Entities\Permissions;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permissions>
 */
class PermissionsFactory extends Factory
{
    protected $model = Permissions::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => $this->faker->word(),
            "code" => $this->faker->unique()->word(),
            "category" => $this->faker->word(),
        ];
    }
}
