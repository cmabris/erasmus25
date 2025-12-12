<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NewsletterSubscription>
 */
class NewsletterSubscriptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->optional()->name(),
            'programs' => fake()->optional()->randomElements(['KA1xx', 'KA121-VET', 'KA131-HED'], fake()->numberBetween(1, 3)),
            'is_active' => true,
            'subscribed_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'unsubscribed_at' => null,
            'verification_token' => Str::random(32),
            'verified_at' => fake()->optional()->dateTimeBetween('-1 year', 'now'),
        ];
    }

    /**
     * Indicate that the subscription is unsubscribed.
     */
    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
            'unsubscribed_at' => fake()->dateTimeBetween($attributes['subscribed_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the subscription is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => null,
        ]);
    }
}
