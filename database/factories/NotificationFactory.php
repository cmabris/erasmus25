<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = [
            'convocatoria' => 'Nueva convocatoria disponible',
            'resolucion' => 'Nueva resolución publicada',
            'noticia' => 'Nueva noticia publicada',
            'revision' => 'Contenido pendiente de revisión',
            'sistema' => 'Notificación del sistema',
        ];

        $type = fake()->randomElement(array_keys($types));

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'title' => $types[$type],
            'message' => fake()->sentence(),
            'link' => fake()->optional()->url(),
            'is_read' => false,
            'read_at' => null,
        ];
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
