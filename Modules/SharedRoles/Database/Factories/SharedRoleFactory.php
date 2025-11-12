<?php

namespace Modules\SharedRoles\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SharedRole>
 */
class SharedRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = json_encode(['en' => $this->faker->word()]);

        return [
            'code' => $this->faker->unique()->word(),
            'name' => $name,
        ];
    }
}
