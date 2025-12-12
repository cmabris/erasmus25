<?php

namespace Database\Factories;

use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MediaConsent>
 */
class MediaConsentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'media_id' => fake()->numberBetween(1, 100), // Will be replaced when Media Library is installed
            'consent_type' => fake()->randomElement(['imagen', 'video', 'audio']),
            'person_name' => fake()->optional()->name(),
            'person_email' => fake()->optional()->safeEmail(),
            'consent_given' => true,
            'consent_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'consent_document_id' => fake()->optional()->randomElement([Document::factory(), null]),
            'expires_at' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'revoked_at' => null,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the consent is revoked.
     */
    public function revoked(): static
    {
        return $this->state(fn (array $attributes) => [
            'consent_given' => false,
            'revoked_at' => fake()->dateTimeBetween($attributes['consent_date'], 'now'),
        ]);
    }
}
