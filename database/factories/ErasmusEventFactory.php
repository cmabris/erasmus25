<?php

namespace Database\Factories;

use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ErasmusEvent>
 */
class ErasmusEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            'apertura' => 'Apertura de convocatoria',
            'cierre' => 'Cierre de convocatoria',
            'entrevista' => 'Entrevistas de selección',
            'publicacion_provisional' => 'Publicación listado provisional',
            'publicacion_definitivo' => 'Publicación listado definitivo',
            'reunion_informativa' => 'Reunión informativa',
            'otro' => 'Otro evento',
        ];

        $eventType = fake()->randomElement(array_keys($eventTypes));

        return [
            'program_id' => fake()->optional()->randomElement([Program::factory(), null]),
            'call_id' => fake()->optional()->randomElement([Call::factory(), null]),
            'title' => $eventTypes[$eventType],
            'description' => fake()->optional()->paragraph(),
            'event_type' => $eventType,
            'start_date' => fake()->dateTimeBetween('now', '+6 months'),
            'end_date' => fake()->optional()->dateTimeBetween('+6 months', '+7 months'),
            'location' => fake()->optional()->address(),
            'is_public' => true,
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the event is private.
     */
    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_public' => false,
        ]);
    }
}
