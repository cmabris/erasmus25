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
        $uniqueId = fake()->unique()->numberBetween(1000, 9999);
        $programTypes = [
            ['name' => 'Educación Escolar', 'codePrefix' => 'KA1'],
            ['name' => 'Formación Profesional', 'codePrefix' => 'VET'],
            ['name' => 'Educación Superior', 'codePrefix' => 'HED'],
        ];

        $type = fake()->randomElement($programTypes);
        $name = $type['name'].' '.$uniqueId;

        return [
            'code' => $type['codePrefix'].'-'.$uniqueId,
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
