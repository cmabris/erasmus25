<?php

namespace Database\Factories;

use App\Models\Program;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement([
            'Educación Escolar',
            'Formación Profesional',
            'Educación Superior',
        ]);

        $codes = [
            'Educación Escolar' => 'KA1xx',
            'Formación Profesional' => 'KA121-VET',
            'Educación Superior' => 'KA131-HED',
        ];

        return [
            'code' => $codes[$name] ?? fake()->unique()->regexify('[A-Z0-9]{6}'),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'is_active' => true,
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the program is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
