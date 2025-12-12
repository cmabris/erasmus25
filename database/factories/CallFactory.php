<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Call>
 */
class CallFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);
        $destinations = [
            fake()->country(),
            fake()->country(),
        ];

        return [
            'program_id' => Program::factory(),
            'academic_year_id' => AcademicYear::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'type' => fake()->randomElement(['alumnado', 'personal']),
            'modality' => fake()->randomElement(['corta', 'larga']),
            'number_of_places' => fake()->numberBetween(5, 30),
            'destinations' => $destinations,
            'estimated_start_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'estimated_end_date' => fake()->dateTimeBetween('+7 months', '+12 months'),
            'requirements' => fake()->paragraphs(3, true),
            'documentation' => fake()->paragraphs(2, true),
            'selection_criteria' => fake()->paragraphs(2, true),
            'scoring_table' => [
                'expediente_academico' => 40,
                'idioma' => 30,
                'entrevista' => 20,
                'otros_meritos' => 10,
            ],
            'status' => fake()->randomElement(['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada']),
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'closed_at' => fake()->optional()->dateTimeBetween('now', '+1 month'),
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the call is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'abierta',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the call is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'borrador',
            'published_at' => null,
        ]);
    }
}
