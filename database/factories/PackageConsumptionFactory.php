<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PackageConsumption>
 */
class PackageConsumptionFactory extends Factory
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
            'consumption_date' => fake()->date(),
            'amount' => fake()->randomFloat(2, 1000, 10000),
        ];
    }
}
