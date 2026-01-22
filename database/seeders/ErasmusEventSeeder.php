<?php

namespace Database\Seeders;

use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ErasmusEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::where('is_active', true)->get();
        $calls = Call::with('program')->get();
        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first();

        if ($programs->isEmpty()) {
            $this->command->warn('No hay programas disponibles. Ejecuta primero ProgramsSeeder.');

            return;
        }

        $eventsData = [];

        // Ubicaciones realistas
        $locations = [
            'Aula Magna - Edificio Principal',
            'Salón de Actos - Centro de Formación',
            'Aula 101 - Edificio A',
            'Sala de Reuniones - Planta 2',
            'Auditorio - Centro Cultural',
            'Aula de Informática - Edificio B',
            'Sala de Conferencias - Biblioteca',
            'Online - Plataforma Teams',
            'Online - Zoom',
            null, // Algunos eventos sin ubicación
        ];

        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // ============================================
        // EVENTOS ASOCIADOS A CONVOCATORIAS
        // ============================================

        foreach ($calls->take(8) as $call) {
            $publishedAt = Carbon::parse($call->published_at);
            $program = $call->program;

            // Evento: Apertura de convocatoria
            $eventsData[] = [
                'program_id' => $call->program_id,
                'call_id' => $call->id,
                'title' => "Apertura de convocatoria: {$call->title}",
                'description' => "Se abre oficialmente la convocatoria de movilidad {$call->title}. ".
                    'Los interesados pueden comenzar a presentar sus solicitudes.',
                'event_type' => 'apertura',
                'start_date' => $publishedAt->copy()->setTime(9, 0),
                'end_date' => null,
                'location' => fake()->randomElement($locations),
                'is_public' => true,
                'created_by' => $adminUser?->id,
            ];

            // Evento: Reunión informativa (1-2 semanas después de apertura)
            $eventsData[] = [
                'program_id' => $call->program_id,
                'call_id' => $call->id,
                'title' => "Reunión informativa: {$call->title}",
                'description' => "Reunión informativa para resolver dudas sobre la convocatoria {$call->title}. ".
                    'Se explicarán los requisitos, documentación necesaria y proceso de selección.',
                'event_type' => 'reunion_informativa',
                'start_date' => $publishedAt->copy()->addWeeks(fake()->numberBetween(1, 2))->setTime(17, 0),
                'end_date' => $publishedAt->copy()->addWeeks(fake()->numberBetween(1, 2))->setTime(19, 0),
                'location' => fake()->randomElement($locations),
                'is_public' => true,
                'created_by' => $adminUser?->id,
            ];

            // Evento: Cierre de convocatoria (si está abierta, 2-3 meses después)
            if ($call->status === 'abierta') {
                $closeDate = $publishedAt->copy()->addMonths(fake()->numberBetween(2, 3));
                $eventsData[] = [
                    'program_id' => $call->program_id,
                    'call_id' => $call->id,
                    'title' => "Cierre de convocatoria: {$call->title}",
                    'description' => "Último día para presentar solicitudes para la convocatoria {$call->title}. ".
                        'Las solicitudes deben entregarse antes de las 14:00 horas.',
                    'event_type' => 'cierre',
                    'start_date' => $closeDate->setTime(14, 0),
                    'end_date' => null,
                    'location' => 'Secretaría - Edificio Principal',
                    'is_public' => true,
                    'created_by' => $adminUser?->id,
                ];

                // Evento: Entrevistas (1-2 semanas después del cierre)
                $eventsData[] = [
                    'program_id' => $call->program_id,
                    'call_id' => $call->id,
                    'title' => "Entrevistas de selección: {$call->title}",
                    'description' => "Entrevistas personales con los candidatos preseleccionados para la convocatoria {$call->title}. ".
                        'Se evaluarán los aspectos complementarios a la documentación presentada.',
                    'event_type' => 'entrevista',
                    'start_date' => $closeDate->copy()->addWeeks(fake()->numberBetween(1, 2))->setTime(9, 0),
                    'end_date' => $closeDate->copy()->addWeeks(fake()->numberBetween(1, 2))->setTime(18, 0),
                    'location' => fake()->randomElement($locations),
                    'is_public' => true,
                    'created_by' => $adminUser?->id,
                ];

                // Evento: Publicación listado provisional (2-3 semanas después de entrevistas)
                $eventsData[] = [
                    'program_id' => $call->program_id,
                    'call_id' => $call->id,
                    'title' => "Publicación listado provisional: {$call->title}",
                    'description' => "Se publica el listado provisional de candidatos seleccionados para la convocatoria {$call->title}. ".
                        'Los interesados pueden consultar el listado y presentar alegaciones en caso de disconformidad.',
                    'event_type' => 'publicacion_provisional',
                    'start_date' => $closeDate->copy()->addWeeks(fake()->numberBetween(3, 4))->setTime(10, 0),
                    'end_date' => null,
                    'location' => null,
                    'is_public' => true,
                    'created_by' => $adminUser?->id,
                ];

                // Evento: Publicación listado definitivo (1-2 semanas después del provisional)
                $eventsData[] = [
                    'program_id' => $call->program_id,
                    'call_id' => $call->id,
                    'title' => "Publicación listado definitivo: {$call->title}",
                    'description' => "Se publica el listado definitivo de candidatos seleccionados para la convocatoria {$call->title}. ".
                        'Este listado es definitivo y no admite más alegaciones.',
                    'event_type' => 'publicacion_definitivo',
                    'start_date' => $closeDate->copy()->addWeeks(fake()->numberBetween(4, 5))->setTime(10, 0),
                    'end_date' => null,
                    'location' => null,
                    'is_public' => true,
                    'created_by' => $adminUser?->id,
                ];
            }
        }

        // ============================================
        // EVENTOS INDEPENDIENTES (REUNIONES INFORMATIVAS GENERALES)
        // ============================================

        foreach ($programs->take(4) as $program) {
            // Reunión informativa general del programa
            $eventsData[] = [
                'program_id' => $program->id,
                'call_id' => null,
                'title' => "Reunión informativa general: {$program->name}",
                'description' => "Reunión informativa sobre el programa {$program->name}. ".
                    'Se explicarán las oportunidades de movilidad, requisitos generales y proceso de participación.',
                'event_type' => 'reunion_informativa',
                'start_date' => $now->copy()->addDays(fake()->numberBetween(5, 30))->setTime(17, 0),
                'end_date' => $now->copy()->addDays(fake()->numberBetween(5, 30))->setTime(19, 0),
                'location' => fake()->randomElement($locations),
                'is_public' => true,
                'created_by' => $adminUser?->id,
            ];
        }

        // ============================================
        // EVENTOS PASADOS (para tener historial)
        // ============================================

        // Eventos pasados del año académico anterior
        for ($i = 0; $i < 8; $i++) {
            $program = $programs->random();
            $pastDate = $now->copy()->subMonths(fake()->numberBetween(3, 8));
            $eventTypes = ['apertura', 'cierre', 'reunion_informativa', 'publicacion_definitivo'];

            $eventsData[] = [
                'program_id' => $program->id,
                'call_id' => null,
                'title' => fake()->randomElement([
                    "Reunión informativa {$program->name}",
                    "Apertura convocatoria {$program->name}",
                    "Cierre convocatoria {$program->name}",
                    "Publicación resultados {$program->name}",
                ]),
                'description' => fake()->optional()->paragraph(),
                'event_type' => fake()->randomElement($eventTypes),
                'start_date' => $pastDate->setTime(fake()->numberBetween(9, 17), fake()->randomElement([0, 30])),
                'end_date' => fake()->optional(0.3)->dateTimeBetween($pastDate, $pastDate->copy()->addHours(2)),
                'location' => fake()->optional(0.7)->randomElement($locations),
                'is_public' => true,
                'created_by' => $adminUser?->id,
            ];
        }

        // ============================================
        // EVENTOS FUTUROS DISTRIBUIDOS EN EL AÑO
        // ============================================

        // Eventos para los próximos 6 meses
        for ($month = 0; $month < 6; $month++) {
            $monthDate = $now->copy()->addMonths($month);
            $daysInMonth = $monthDate->daysInMonth;

            // 2-4 eventos por mes
            $eventsPerMonth = fake()->numberBetween(2, 4);

            for ($e = 0; $e < $eventsPerMonth; $e++) {
                $program = $programs->random();
                $day = fake()->numberBetween(1, $daysInMonth);
                $eventDate = $monthDate->copy()->setDay($day);

                // Evitar eventos en fines de semana (opcional, algunos sí)
                if (fake()->boolean(70)) {
                    // Asegurar día laboral
                    while ($eventDate->isWeekend()) {
                        $day = fake()->numberBetween(1, $daysInMonth);
                        $eventDate = $monthDate->copy()->setDay($day);
                    }
                }

                $eventTypes = [
                    'reunion_informativa',
                    'otro',
                    'apertura',
                    'cierre',
                ];

                $eventType = fake()->randomElement($eventTypes);
                $title = match ($eventType) {
                    'reunion_informativa' => "Reunión informativa {$program->name}",
                    'apertura' => "Apertura convocatoria {$program->name}",
                    'cierre' => "Cierre convocatoria {$program->name}",
                    default => fake()->randomElement([
                        "Jornada de puertas abiertas {$program->name}",
                        "Taller informativo {$program->name}",
                        'Charla sobre movilidad internacional',
                        'Presentación de experiencias Erasmus+',
                    ]),
                };

                $eventsData[] = [
                    'program_id' => $program->id,
                    'call_id' => null,
                    'title' => $title,
                    'description' => fake()->optional(0.8)->paragraph(),
                    'event_type' => $eventType,
                    'start_date' => $eventDate->setTime(fake()->numberBetween(9, 17), fake()->randomElement([0, 30])),
                    'end_date' => fake()->optional(0.5)->dateTimeBetween($eventDate, $eventDate->copy()->addHours(3)),
                    'location' => fake()->optional(0.8)->randomElement($locations),
                    'is_public' => true,
                    'created_by' => $adminUser?->id,
                ];
            }
        }

        // ============================================
        // EVENTOS ESPECIALES (HOY Y PRÓXIMOS DÍAS)
        // ============================================

        // Evento hoy
        $eventsData[] = [
            'program_id' => $programs->random()->id,
            'call_id' => null,
            'title' => 'Reunión informativa - Movilidad Internacional',
            'description' => 'Reunión informativa sobre las oportunidades de movilidad internacional disponibles este año académico.',
            'event_type' => 'reunion_informativa',
            'start_date' => $now->copy()->setTime(17, 0),
            'end_date' => $now->copy()->setTime(19, 0),
            'location' => 'Aula Magna - Edificio Principal',
            'is_public' => true,
            'created_by' => $adminUser?->id,
        ];

        // Evento mañana
        $eventsData[] = [
            'program_id' => $programs->random()->id,
            'call_id' => null,
            'title' => 'Taller: Preparación de solicitudes Erasmus+',
            'description' => 'Taller práctico sobre cómo preparar una solicitud completa para programas Erasmus+. '.
                'Se revisarán los documentos necesarios y se darán consejos prácticos.',
            'event_type' => 'otro',
            'start_date' => $now->copy()->addDay()->setTime(10, 0),
            'end_date' => $now->copy()->addDay()->setTime(13, 0),
            'location' => 'Aula de Informática - Edificio B',
            'is_public' => true,
            'created_by' => $adminUser?->id,
        ];

        // Evento esta semana
        $eventsData[] = [
            'program_id' => $programs->random()->id,
            'call_id' => null,
            'title' => 'Charla: Experiencias de movilidad',
            'description' => 'Charla con estudiantes y personal que han participado en programas Erasmus+. '.
                'Compartirán sus experiencias y responderán preguntas.',
            'event_type' => 'otro',
            'start_date' => $now->copy()->addDays(fake()->numberBetween(2, 5))->setTime(16, 0),
            'end_date' => $now->copy()->addDays(fake()->numberBetween(2, 5))->setTime(18, 0),
            'location' => 'Salón de Actos - Centro de Formación',
            'is_public' => true,
            'created_by' => $adminUser?->id,
        ];

        // ============================================
        // CREAR EVENTOS
        // ============================================

        foreach ($eventsData as $eventData) {
            ErasmusEvent::updateOrCreate(
                [
                    'title' => $eventData['title'],
                    'start_date' => $eventData['start_date'],
                ],
                $eventData
            );
        }

        $this->command->info('Eventos creados: '.count($eventsData));
        $this->command->info('  - Eventos asociados a convocatorias: '.count(array_filter($eventsData, fn ($e) => ! empty($e['call_id']))));
        $this->command->info('  - Eventos independientes: '.count(array_filter($eventsData, fn ($e) => empty($e['call_id']))));
        $this->command->info('  - Eventos pasados: '.count(array_filter($eventsData, fn ($e) => Carbon::parse($e['start_date'])->isPast())));
        $this->command->info('  - Eventos futuros: '.count(array_filter($eventsData, fn ($e) => Carbon::parse($e['start_date'])->isFuture())));
    }
}
