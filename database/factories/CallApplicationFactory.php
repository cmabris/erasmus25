<?php

namespace Database\Factories;

use App\Models\Call;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CallApplication>
 */
class CallApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'call_id' => Call::factory(),
            'applicant_name' => fake()->name(),
            'applicant_email' => fake()->unique()->safeEmail(),
            'applicant_phone' => fake()->optional()->phoneNumber(),
            'status' => fake()->randomElement(['pendiente', 'admitida', 'rechazada', 'renunciada']),
            'score' => fake()->optional()->randomFloat(2, 0, 100),
            'position' => fake()->optional()->numberBetween(1, 50),
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    /**
     * Indicate that the application is admitted.
     */
    public function admitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'admitida',
            'score' => fake()->randomFloat(2, 50, 100),
            'position' => fake()->numberBetween(1, 30),
        ]);
    }

    /**
     * Indicate that the application is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rechazada',
            'score' => fake()->randomFloat(2, 0, 50),
        ]);
    }
}
