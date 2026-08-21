<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MisReport>
 */
class MisReportFactory extends Factory
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
            'report_date' => fake()->date(),
            'occupancy' => fake()->numberBetween(1, 10),
            'occupancy_pct' => fake()->randomFloat(2, 10, 100),
            'admission' => fake()->numberBetween(5, 30),
            'discharge' => fake()->numberBetween(5, 30),
            'total_op' => fake()->numberBetween(50, 300),
            'er_count' => fake()->numberBetween(10, 50),
        ];
    }
}
