<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['string', 'integer', 'boolean', 'json']);
        $group = fake()->randomElement(['general', 'email', 'rgpd', 'media', 'seo']);

        $value = match ($type) {
            'integer' => (string) fake()->numberBetween(1, 100),
            'boolean' => fake()->boolean() ? '1' : '0',
            'json' => json_encode(['key' => 'value']),
            default => fake()->sentence(),
        };

        return [
            'key' => fake()->unique()->slug(),
            'value' => $value,
            'type' => $type,
            'group' => $group,
            'description' => fake()->optional()->sentence(),
            'updated_by' => fake()->optional()->randomElement([User::factory(), null]),
        ];
    }
}
