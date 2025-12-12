<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AcademicYear>
 */
class AcademicYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2020, 2030);
        $endYear = $startYear + 1;

        return [
            'year' => "{$startYear}-{$endYear}",
            'start_date' => fake()->dateTimeBetween("-{$startYear}-09-01", "-{$startYear}-09-15"),
            'end_date' => fake()->dateTimeBetween("{$endYear}-06-15", "{$endYear}-06-30"),
            'is_current' => false,
        ];
    }

    /**
     * Indicate that the academic year is current.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
