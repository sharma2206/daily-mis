<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BillItem>
 */
class BillItemFactory extends Factory
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
            'bill_date' => fake()->date(),
            'patient_id' => fake()->unique()->numerify('PAT-####'),
            'patient_type' => fake()->randomElement(['OP', 'IP', 'ER']),
            'service_type' => fake()->randomElement(['Pharmacy', 'OP Consultation', 'Laboratory', 'Radiology']),
            'sub_department' => fake()->word(),
            'amount' => fake()->randomFloat(2, 100, 5000),
            'net_amount' => function (array $attributes) {
                return $attributes['amount'];
            },
            'quantity' => fake()->numberBetween(1, 5),
            'status' => fake()->randomElement(['Sale', 'Refund']),
        ];
    }
}
