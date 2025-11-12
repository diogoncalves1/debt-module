<?php

namespace Modules\Currency\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code =  $this->faker->unique()->currencyCode();


        $currencies = [
            'USD' => ['symbol' => '$', 'info' => '{"en": {"name": "United States Dollar"}}'],
            'EUR' => ['symbol' => '€', 'info' => '{"en": {"name": "Euro"}}'],
            'GBP' => ['symbol' => '£', 'info' => '{"en": {"name": "British Pound Sterling"}}'],
            'JPY' => ['symbol' => '¥', 'info' => '{"en": {"name": "Japanese Yen"}}'],
            'AUD' => ['symbol' => 'A$', 'info' => '{"en": {"name": "Australian Dollar"}}'],
            'CAD' => ['symbol' => 'C$', 'info' => '{"en": {"name": "Canadian Dollar"}}'],
        ];

        $symbol = $currencies[$code]['symbol'] ?? '$';
        $info = $currencies[$code]['info'] ?? '{"en": {"name": "United States Dollar"}}';

        return [
            "code" => $code,
            "symbol" => $symbol,
            "info" => $info,
            "rate" => $this->faker->randomFloat(4, 0.1, 5)
        ];
    }
}
