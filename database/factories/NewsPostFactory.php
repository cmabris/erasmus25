<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsPost>
 */
class NewsPostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(6);
        
        // Use firstOrCreate to avoid duplicate academic years in parallel tests
        $startYear = fake()->numberBetween(2000, 2100);
        $yearString = "{$startYear}-".($startYear + 1);
        
        $academicYear = AcademicYear::firstOrCreate(
            ['year' => $yearString],
            [
                'year' => $yearString,
                'start_date' => fake()->dateTimeBetween("-{$startYear}-09-01", "-{$startYear}-09-15"),
                'end_date' => fake()->dateTimeBetween(($startYear + 1)."-06-15", ($startYear + 1)."-06-30"),
                'is_current' => false,
            ]
        );

        return [
            'program_id' => Program::factory(),
            'academic_year_id' => $academicYear->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'country' => fake()->optional()->country(),
            'city' => fake()->optional()->city(),
            'host_entity' => fake()->optional()->company(),
            'mobility_type' => fake()->optional()->randomElement(['alumnado', 'personal']),
            'mobility_category' => fake()->optional()->randomElement(['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro']),
            'status' => fake()->randomElement(['borrador', 'en_revision', 'publicado', 'archivado']),
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'author_id' => User::factory(),
            'reviewed_by' => fake()->optional()->randomElement([User::factory(), null]),
            'reviewed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * Indicate that the news post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'publicado',
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the news post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'borrador',
            'published_at' => null,
        ]);
    }
}
