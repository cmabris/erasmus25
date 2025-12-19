<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\Resolution;
use App\Models\User;
use Illuminate\Database\Seeder;

class ResolutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener convocatorias cerradas que tengan fases
        $calls = Call::where('status', 'cerrada')
            ->whereNotNull('published_at')
            ->whereNotNull('closed_at')
            ->with('phases')
            ->get();

        if ($calls->isEmpty()) {
            $this->command->warn('No hay convocatorias cerradas disponibles. Ejecuta primero CallSeeder y CallPhaseSeeder.');

            return;
        }

        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first();
        $resolutionsCreated = 0;

        foreach ($calls as $call) {
            $phases = $call->phases;

            // Resolución provisional (asociada a fase provisional)
            $provisionalPhase = $phases->where('phase_type', 'provisional')->first();
            if ($provisionalPhase) {
                Resolution::firstOrCreate(
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $provisionalPhase->id,
                        'type' => 'provisional',
                    ],
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $provisionalPhase->id,
                        'type' => 'provisional',
                        'title' => "Resolución provisional de la convocatoria: {$call->title}",
                        'description' => "Se publica el listado provisional de candidatos seleccionados y suplentes para la convocatoria {$call->title}. ".
                            'Los candidatos tienen un plazo de 10 días hábiles para presentar alegaciones.',
                        'evaluation_procedure' => "Procedimiento de evaluación:\n\n".
                            "1. Recepción de solicitudes: {$call->published_at?->format('d/m/Y')}\n".
                            "2. Evaluación de candidaturas según baremo establecido\n".
                            "3. Publicación de listado provisional: {$provisionalPhase->start_date?->format('d/m/Y')}\n".
                            "4. Periodo de alegaciones: 10 días hábiles\n".
                            '5. Resolución de alegaciones y publicación de listado definitivo',
                        'official_date' => $provisionalPhase->start_date?->copy(),
                        'published_at' => $provisionalPhase->start_date?->copy(),
                        'created_by' => $adminUser?->id,
                    ]
                );
                $resolutionsCreated++;
            }

            // Resolución definitiva (asociada a fase definitiva)
            $definitivoPhase = $phases->where('phase_type', 'definitivo')->first();
            if ($definitivoPhase) {
                Resolution::firstOrCreate(
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $definitivoPhase->id,
                        'type' => 'definitivo',
                    ],
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $definitivoPhase->id,
                        'type' => 'definitivo',
                        'title' => "Resolución definitiva de la convocatoria: {$call->title}",
                        'description' => "Se publica el listado definitivo de candidatos seleccionados para la convocatoria {$call->title}, ".
                            'tras la resolución de las alegaciones presentadas al listado provisional.',
                        'evaluation_procedure' => "Procedimiento de evaluación completado:\n\n".
                            "1. Recepción de solicitudes: {$call->published_at?->format('d/m/Y')}\n".
                            "2. Evaluación de candidaturas según baremo establecido\n".
                            "3. Publicación de listado provisional\n".
                            "4. Resolución de alegaciones presentadas\n".
                            "5. Publicación de listado definitivo: {$definitivoPhase->start_date?->format('d/m/Y')}\n\n".
                            'Los candidatos seleccionados deberán confirmar su participación en el plazo establecido.',
                        'official_date' => $definitivoPhase->start_date?->copy(),
                        'published_at' => $definitivoPhase->start_date?->copy(),
                        'created_by' => $adminUser?->id,
                    ]
                );
                $resolutionsCreated++;
            }

            // Resolución sobre alegaciones (si existe fase de alegaciones)
            $alegacionesPhase = $phases->where('phase_type', 'alegaciones')->first();
            if ($alegacionesPhase && fake()->boolean(30)) { // Solo 30% de las convocatorias tienen resolución de alegaciones
                Resolution::firstOrCreate(
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $alegacionesPhase->id,
                        'type' => 'alegaciones',
                    ],
                    [
                        'call_id' => $call->id,
                        'call_phase_id' => $alegacionesPhase->id,
                        'type' => 'alegaciones',
                        'title' => "Resolución sobre alegaciones presentadas: {$call->title}",
                        'description' => "Resolución sobre las alegaciones presentadas al listado provisional de la convocatoria {$call->title}.",
                        'evaluation_procedure' => 'Se han resuelto todas las alegaciones presentadas durante el periodo establecido. '.
                            'Las decisiones adoptadas se reflejan en el listado definitivo publicado.',
                        'official_date' => $alegacionesPhase->end_date?->copy()->addDays(2),
                        'published_at' => $alegacionesPhase->end_date?->copy()->addDays(2),
                        'created_by' => $adminUser?->id,
                    ]
                );
                $resolutionsCreated++;
            }
        }

        $this->command->info("Resoluciones creadas: {$resolutionsCreated} para ".$calls->count().' convocatorias cerradas');

        // Nota: Para añadir PDFs a las resoluciones usando Laravel Media Library:
        // foreach (Resolution::all() as $resolution) {
        //     if (!$resolution->hasMedia('pdf')) {
        //         $resolution->addMediaFromUrl('https://example.com/sample.pdf')
        //             ->toMediaCollection('pdf');
        //     }
        // }
    }
}
