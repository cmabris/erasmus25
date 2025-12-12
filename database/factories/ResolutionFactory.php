<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\CallPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resolution>
 */
class ResolutionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['provisional', 'definitivo', 'alegaciones']);
        $titles = [
            'provisional' => 'Resolución provisional de la convocatoria',
            'definitivo' => 'Resolución definitiva de la convocatoria',
            'alegaciones' => 'Resolución sobre alegaciones presentadas',
        ];

        return [
            'call_id' => Call::factory(),
            'call_phase_id' => CallPhase::factory(),
            'type' => $type,
            'title' => $titles[$type],
            'description' => fake()->paragraphs(2, true),
            'evaluation_procedure' => fake()->paragraphs(3, true),
            'official_date' => fake()->dateTimeBetween('-1 month', 'now'),
            'published_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the resolution is provisional.
     */
    public function provisional(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'provisional',
            'title' => 'Resolución provisional de la convocatoria',
        ]);
    }

    /**
     * Indicate that the resolution is definitive.
     */
    public function definitive(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'definitivo',
            'title' => 'Resolución definitiva de la convocatoria',
        ]);
    }
}
