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
        // Use a wider range to reduce collisions in parallel tests
        // Generate a unique year combination with wider range to reduce collisions
        $startYear = fake()->numberBetween(2000, 2100);
        $yearString = "{$startYear}-".($startYear + 1);

        return [
            'year' => $yearString,
            'start_date' => fake()->dateTimeBetween("-{$startYear}-09-01", "-{$startYear}-09-15"),
            'end_date' => fake()->dateTimeBetween(($startYear + 1).'-06-15', ($startYear + 1).'-06-30'),
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
