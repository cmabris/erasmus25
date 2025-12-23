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

        // Use firstOrCreate to avoid duplicate academic years in parallel tests
        $academicYearId = null;
        if (fake()->boolean(70)) { // 70% chance of having academic year
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
            $academicYearId = $academicYear->id;
        }

        return [
            'category_id' => DocumentCategory::factory(),
            'program_id' => fake()->optional()->randomElement([Program::factory(), null]),
            'academic_year_id' => $academicYearId,
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
