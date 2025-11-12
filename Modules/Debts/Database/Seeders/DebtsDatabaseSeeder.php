<?php

namespace Modules\Debts\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Currency\Entities\Currency;
use Modules\Debts\Entities\Debt;

class DebtsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Debt::create([
            'name' => 'teste',
            'total_amount' => 122,
            'paid_amount' => 12,
            'status' =>  'pending',
            'installments' => 2,
            'insterest_rate' => 2,
            'start_date' => date('Y-m-d'),
            'due_date' => date('Y-m-d'),
            'currency_id' => Currency::pluck('id')->random(),
            'period' => 'daily',
        ]);

        $this->call([
            DebtSeeder::class,
            DebtPaymentSeeder::class
        ]);
    }
}
