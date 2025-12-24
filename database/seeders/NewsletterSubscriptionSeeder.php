<?php

namespace Database\Seeders;

use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsletterSubscriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener programas activos para usar sus códigos
        $programs = Program::where('is_active', true)->pluck('code')->toArray();

        if (empty($programs)) {
            $this->command->warn('No hay programas activos disponibles. Ejecuta primero ProgramsSeeder.');

            return;
        }

        $totalSubscriptions = 80;
        $verifiedActive = (int) ($totalSubscriptions * 0.6); // 60% = 48
        $unverified = (int) ($totalSubscriptions * 0.2); // 20% = 16
        $unsubscribed = $totalSubscriptions - $verifiedActive - $unverified; // 20% = 16

        $this->command->info("Creando {$totalSubscriptions} suscripciones de newsletter...");
        $this->command->info("- Verificadas y activas: {$verifiedActive}");
        $this->command->info("- Sin verificar: {$unverified}");
        $this->command->info("- Dadas de baja: {$unsubscribed}");

        // 1. Suscripciones verificadas y activas (60%)
        for ($i = 0; $i < $verifiedActive; $i++) {
            $subscribedAt = now()->subMonths(fake()->numberBetween(1, 6))->subDays(fake()->numberBetween(0, 30));
            $verifiedAt = $subscribedAt->copy()->addHours(fake()->numberBetween(1, 48));

            NewsletterSubscription::create([
                'email' => fake()->unique()->safeEmail(),
                'name' => fake()->optional(0.7)->name(),
                'programs' => $this->getRandomPrograms($programs),
                'is_active' => true,
                'subscribed_at' => $subscribedAt,
                'unsubscribed_at' => null,
                'verification_token' => Str::random(32),
                'verified_at' => $verifiedAt,
            ]);
        }

        // 2. Suscripciones sin verificar (20%)
        for ($i = 0; $i < $unverified; $i++) {
            $subscribedAt = now()->subDays(fake()->numberBetween(1, 30));

            NewsletterSubscription::create([
                'email' => fake()->unique()->safeEmail(),
                'name' => fake()->optional(0.6)->name(),
                'programs' => $this->getRandomPrograms($programs),
                'is_active' => false,
                'subscribed_at' => $subscribedAt,
                'unsubscribed_at' => null,
                'verification_token' => Str::random(32),
                'verified_at' => null,
            ]);
        }

        // 3. Suscripciones dadas de baja (20%)
        for ($i = 0; $i < $unsubscribed; $i++) {
            $subscribedAt = now()->subMonths(fake()->numberBetween(2, 6))->subDays(fake()->numberBetween(0, 30));
            $verifiedAt = $subscribedAt->copy()->addHours(fake()->numberBetween(1, 48));
            $unsubscribedAt = $verifiedAt->copy()->addDays(fake()->numberBetween(7, 90));

            NewsletterSubscription::create([
                'email' => fake()->unique()->safeEmail(),
                'name' => fake()->optional(0.7)->name(),
                'programs' => $this->getRandomPrograms($programs),
                'is_active' => false,
                'subscribed_at' => $subscribedAt,
                'unsubscribed_at' => $unsubscribedAt,
                'verification_token' => Str::random(32),
                'verified_at' => $verifiedAt,
            ]);
        }

        $this->command->info("✓ {$totalSubscriptions} suscripciones creadas exitosamente.");
    }

    /**
     * Get random programs selection.
     *
     * @param  array<int, string>  $availablePrograms
     * @return array<int, string>|null
     */
    protected function getRandomPrograms(array $availablePrograms): ?array
    {
        $selection = fake()->randomElement([
            'all', // 30% - todos los programas
            'some', // 50% - algunos programas
            'none', // 20% - sin programas
        ]);

        return match ($selection) {
            'all' => $availablePrograms,
            'some' => fake()->randomElements($availablePrograms, fake()->numberBetween(1, min(3, count($availablePrograms)))),
            'none' => null,
        };
    }
}

