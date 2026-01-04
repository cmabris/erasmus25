<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentCategory>
 */
class DocumentCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $baseNames = [
            'Convocatorias',
            'Modelos',
            'Seguros',
            'Consentimientos',
            'Guías',
            'FAQ',
            'Otros',
        ];

        // Usar un nombre base aleatorio y añadir un sufijo único para evitar duplicados
        $baseName = fake()->randomElement($baseNames);
        $uniqueSuffix = fake()->unique()->numberBetween(1, 999999);
        $name = $baseName.' '.$uniqueSuffix;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'order' => fake()->numberBetween(0, 10),
        ];
    }
}
