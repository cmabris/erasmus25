<?php

namespace Database\Factories;

use App\Models\Call;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CallPhase>
 */
class CallPhaseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $phaseTypes = [
            'publicacion' => 'Publicación',
            'solicitudes' => 'Periodo de solicitudes',
            'provisional' => 'Listado provisional',
            'alegaciones' => 'Periodo de alegaciones',
            'definitivo' => 'Listado definitivo',
            'renuncias' => 'Renuncias',
            'lista_espera' => 'Lista de espera',
        ];

        $phaseType = fake()->randomElement(array_keys($phaseTypes));

        return [
            'call_id' => Call::factory(),
            'phase_type' => $phaseType,
            'name' => $phaseTypes[$phaseType],
            'description' => fake()->optional()->paragraph(),
            'start_date' => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'end_date' => fake()->optional()->dateTimeBetween('+1 month', '+2 months'),
            'is_current' => false,
            'order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the phase is current.
     */
    public function current(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_current' => true,
        ]);
    }
}
