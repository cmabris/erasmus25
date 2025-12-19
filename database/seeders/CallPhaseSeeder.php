<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\CallPhase;
use Illuminate\Database\Seeder;

class CallPhaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $calls = Call::whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->get();

        if ($calls->isEmpty()) {
            $this->command->warn('No hay convocatorias disponibles. Ejecuta primero CallSeeder.');

            return;
        }

        $phasesCreated = 0;

        foreach ($calls as $call) {
            // Fases típicas de una convocatoria
            $phases = [];

            // Fase 1: Publicación
            $phases[] = [
                'call_id' => $call->id,
                'phase_type' => 'publicacion',
                'name' => 'Publicación de la convocatoria',
                'description' => 'La convocatoria ha sido publicada y está disponible para consulta.',
                'start_date' => $call->published_at?->copy(),
                'end_date' => $call->published_at?->copy()->addDays(7),
                'is_current' => false,
                'order' => 1,
            ];

            // Fase 2: Periodo de solicitudes
            $solicitudesStart = $call->published_at?->copy()->addDays(7);
            $solicitudesEnd = $solicitudesStart?->copy()->addDays(30);
            $isSolicitudesCurrent = $call->status === 'abierta' && now()->between($solicitudesStart ?? now(), $solicitudesEnd ?? now());

            $phases[] = [
                'call_id' => $call->id,
                'phase_type' => 'solicitudes',
                'name' => 'Periodo de solicitudes',
                'description' => 'Periodo abierto para la presentación de solicitudes. Todas las solicitudes deben presentarse antes de la fecha límite.',
                'start_date' => $solicitudesStart,
                'end_date' => $solicitudesEnd,
                'is_current' => $isSolicitudesCurrent,
                'order' => 2,
            ];

            // Fase 3: Listado provisional (solo para convocatorias cerradas o en proceso)
            if ($call->status === 'cerrada' || $call->closed_at) {
                $provisionalStart = $call->closed_at?->copy()->addDays(15);
                $provisionalEnd = $provisionalStart?->copy()->addDays(7);

                $phases[] = [
                    'call_id' => $call->id,
                    'phase_type' => 'provisional',
                    'name' => 'Listado provisional',
                    'description' => 'Publicación del listado provisional de candidatos seleccionados y suplentes.',
                    'start_date' => $provisionalStart,
                    'end_date' => $provisionalEnd,
                    'is_current' => false,
                    'order' => 3,
                ];

                // Fase 4: Periodo de alegaciones
                $alegacionesStart = $provisionalEnd?->copy()->addDay();
                $alegacionesEnd = $alegacionesStart?->copy()->addDays(10);

                $phases[] = [
                    'call_id' => $call->id,
                    'phase_type' => 'alegaciones',
                    'name' => 'Periodo de alegaciones',
                    'description' => 'Periodo para presentar alegaciones al listado provisional.',
                    'start_date' => $alegacionesStart,
                    'end_date' => $alegacionesEnd,
                    'is_current' => false,
                    'order' => 4,
                ];

                // Fase 5: Listado definitivo
                $definitivoStart = $alegacionesEnd?->copy()->addDays(5);
                $definitivoEnd = $definitivoStart?->copy()->addDays(7);

                $phases[] = [
                    'call_id' => $call->id,
                    'phase_type' => 'definitivo',
                    'name' => 'Listado definitivo',
                    'description' => 'Publicación del listado definitivo de candidatos seleccionados tras el periodo de alegaciones.',
                    'start_date' => $definitivoStart,
                    'end_date' => $definitivoEnd,
                    'is_current' => false,
                    'order' => 5,
                ];

                // Fase 6: Renuncias y lista de espera
                $renunciasStart = $definitivoEnd?->copy()->addDays(7);
                $renunciasEnd = $renunciasStart?->copy()->addDays(14);

                $phases[] = [
                    'call_id' => $call->id,
                    'phase_type' => 'renuncias',
                    'name' => 'Renuncias y lista de espera',
                    'description' => 'Periodo para gestionar renuncias y cubrir plazas vacantes desde la lista de espera.',
                    'start_date' => $renunciasStart,
                    'end_date' => $renunciasEnd,
                    'is_current' => false,
                    'order' => 6,
                ];
            }

            // Crear fases para esta convocatoria
            foreach ($phases as $phaseData) {
                // Solo crear si no existe ya una fase con el mismo tipo y orden
                CallPhase::firstOrCreate(
                    [
                        'call_id' => $phaseData['call_id'],
                        'phase_type' => $phaseData['phase_type'],
                        'order' => $phaseData['order'],
                    ],
                    $phaseData
                );
            }

            $phasesCreated += count($phases);
        }

        $this->command->info("Fases creadas: {$phasesCreated} para ".$calls->count().' convocatorias');
    }
}
