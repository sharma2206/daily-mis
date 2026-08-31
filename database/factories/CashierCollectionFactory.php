<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CashierCollection>
 */
class CashierCollectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'branch' => fake()->randomElement(['chromepet', 'oragadam']),
            'collection_date' => fake()->date(),
            'patient_type' => fake()->randomElement(['OP', 'IP', 'ER', null]),
            'user_department' => fake()->word(),
            'paid_amount' => fake()->randomFloat(2, 50, 2000),
        ];
    }
}
