<?php

namespace Database\Factories;

use App\Models\AcademicYear;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'category_id' => DocumentCategory::factory(),
            'program_id' => fake()->optional()->randomElement([Program::factory(), null]),
            'academic_year_id' => fake()->optional()->randomElement([AcademicYear::factory(), null]),
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => fake()->optional()->paragraph(),
            'document_type' => fake()->randomElement(['convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro']),
            'version' => fake()->optional()->randomElement(['1.0', '2.0', '1.1', null]),
            'is_active' => true,
            'download_count' => fake()->numberBetween(0, 1000),
            'created_by' => User::factory(),
            'updated_by' => fake()->optional()->randomElement([User::factory(), null]),
        ];
    }

    /**
     * Indicate that the document is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
