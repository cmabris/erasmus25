<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Translation>
 */
class TranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $modelType = fake()->randomElement([
            'App\Models\Call',
            'App\Models\NewsPost',
            'App\Models\Document',
        ]);

        $field = fake()->randomElement(['title', 'description', 'content', 'excerpt']);

        return [
            'translatable_type' => $modelType,
            'translatable_id' => fake()->numberBetween(1, 100),
            'language_id' => Language::factory(),
            'field' => $field,
            'value' => match ($field) {
                'title' => fake()->sentence(),
                'description', 'excerpt' => fake()->paragraph(),
                'content' => fake()->paragraphs(3, true),
                default => fake()->text(),
            },
        ];
    }
}
