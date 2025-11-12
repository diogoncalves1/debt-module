<?php

namespace Modules\Accounts\Database\Factories;

use Modules\Accounts\Entities\Account;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\SharedRoles\Entities\SharedRole;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccountUser>
 */
class AccountUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::pluck('id')->random(),
            'account_id' => Account::pluck('id')->random(),
            'shared_role_id' => SharedRole::pluck('id')->random(),
            'status' => $this->faker->randomElement(['pending', 'accepted', 'revoked'])
        ];
    }
}
