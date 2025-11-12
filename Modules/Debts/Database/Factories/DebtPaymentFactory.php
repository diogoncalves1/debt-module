<?php

namespace Modules\Debts\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Accounts\Entities\Transaction;
use Modules\Currency\Entities\Currency;
use Modules\Debts\Entities\Debt;
use Modules\User\Entities\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DebtPayment>
 */
class DebtPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = $this->faker->randomElement(['pending', 'paid']);

        return [
            "user_id" => User::pluck("id")->random(),
            "debt_id" => Debt::pluck("id")->random(),
            "status" => $status,
            "transaction_id" => $status == 'paid' ? Transaction::factory(1)->create()->id : null,
            'date' => $this->faker->date(),
            'description' => $this->faker->sentence(),
            'amount' => $this->faker->randomFloat(2),
        ];
    }
}
