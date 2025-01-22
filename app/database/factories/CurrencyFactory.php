<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

class CurrencyFactory extends Factory
{
    protected $model = Currency::class;

    public function definition()
    {
        return [
            'code' => $this->faker->currencyCode(),
            'exchange_rate' => $this->faker->randomFloat(4, 0.5, 1.5),
        ];
    }
}
