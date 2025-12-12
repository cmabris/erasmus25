<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['create', 'update', 'delete', 'publish', 'archive', 'restore']);
        $modelType = fake()->randomElement([
            'App\Models\Call',
            'App\Models\NewsPost',
            'App\Models\Document',
            'App\Models\Resolution',
        ]);

        return [
            'user_id' => fake()->optional()->randomElement([User::factory(), null]),
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => fake()->numberBetween(1, 100),
            'changes' => [
                'before' => ['status' => 'borrador'],
                'after' => ['status' => 'publicado'],
            ],
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
