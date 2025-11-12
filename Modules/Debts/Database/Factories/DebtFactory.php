<?php
namespace Database\Factories\Modules\Debts\Entities\DebtFactory;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Currency\Entities\Currency;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $totalAmount = $this->faker->randomFloat('2');
        $paidAmount  = $totalAmount - $this->faker->randomFloat(2, 0, $totalAmount);

        $dueDate   = $this->faker->date();
        $startDate = $this->faker->date('Y-m-d', $dueDate);

        return [
            'name'           => $this->faker->word(),
            'total_amount'   => $totalAmount,
            'paid_amount'    => $paidAmount,
            'status'         => $paidAmount == $totalAmount ? 'paid' : 'pending',
            'installments'   => $this->faker->randomNumber(2),
            'insterest_rate' => $this->faker->randomFloat(2, 0, 10),
            'start_date'     => $startDate,
            'due_date'       => $dueDate,
            'currency_id'    => Currency::pluck('id')->random(),
            'paid_at'        => $paidAmount == $totalAmount ? $this->faker->date('Y-m-d', $dueDate) : null,
            'period'         => $this->faker->randomElement(['daily', 'weekly', 'monthly', 'biannual', 'annual']),
        ];
    }
}
