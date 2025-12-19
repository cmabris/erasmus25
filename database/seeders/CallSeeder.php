<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CallSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener programas activos y años académicos
        $programs = Program::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first();

        if ($programs->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No hay programas o años académicos disponibles. Ejecuta primero ProgramsSeeder y AcademicYearsSeeder.');

            return;
        }

        // Destinos europeos realistas
        $europeanCountries = [
            'Alemania', 'Austria', 'Bélgica', 'Bulgaria', 'Chipre', 'Croacia',
            'Dinamarca', 'Eslovaquia', 'Eslovenia', 'España', 'Estonia', 'Finlandia',
            'Francia', 'Grecia', 'Hungría', 'Irlanda', 'Italia', 'Letonia',
            'Lituania', 'Luxemburgo', 'Malta', 'Países Bajos', 'Polonia', 'Portugal',
            'República Checa', 'Rumanía', 'Suecia',
        ];

        // Convocatorias para diferentes programas
        $callsData = [];

        // Convocatorias abiertas (estado: abierta, published_at: reciente)
        foreach ($programs->take(4) as $index => $program) {
            $year = $academicYears->where('is_current', true)->first() ?? $academicYears->first();
            $destinations = fake()->randomElements($europeanCountries, fake()->numberBetween(3, 8));

            $callsData[] = [
                'program_id' => $program->id,
                'academic_year_id' => $year->id,
                'title' => "Convocatoria de movilidad {$program->name} - {$year->year}",
                'slug' => Str::slug("convocatoria-movilidad-{$program->slug}-{$year->year}"),
                'type' => fake()->randomElement(['alumnado', 'personal']),
                'modality' => fake()->randomElement(['corta', 'larga']),
                'number_of_places' => fake()->numberBetween(10, 25),
                'destinations' => $destinations,
                'estimated_start_date' => now()->addMonths(2),
                'estimated_end_date' => now()->addMonths(8),
                'requirements' => "Requisitos para participar en esta convocatoria:\n\n".
                    "1. Estar matriculado/contratado en el centro educativo.\n".
                    "2. Tener un nivel mínimo de idioma B1 (según el destino).\n".
                    "3. Presentar documentación completa antes de la fecha límite.\n".
                    "4. Cumplir con los criterios específicos del programa.\n\n".
                    'Para más información, consulta la documentación oficial.',
                'documentation' => "Documentación necesaria:\n\n".
                    "- Formulario de solicitud cumplimentado\n".
                    "- CV actualizado en formato Europass\n".
                    "- Carta de motivación\n".
                    "- Certificado de idiomas (nivel B1 mínimo)\n".
                    "- Certificado académico o laboral\n".
                    '- Documento de identidad o pasaporte en vigor',
                'selection_criteria' => "Criterios de selección:\n\n".
                    "1. Expediente académico o profesional (40%)\n".
                    "2. Nivel de idiomas (30%)\n".
                    "3. Entrevista personal (20%)\n".
                    "4. Otros méritos (10%)\n\n".
                    'La comisión evaluadora valorará cada candidatura según estos criterios.',
                'scoring_table' => [
                    'expediente_academico' => 40,
                    'idioma' => 30,
                    'entrevista' => 20,
                    'otros_meritos' => 10,
                ],
                'status' => 'abierta',
                'published_at' => now()->subDays(fake()->numberBetween(5, 30)),
                'closed_at' => null,
                'created_by' => $adminUser?->id,
                'updated_by' => $adminUser?->id,
            ];
        }

        // Convocatorias cerradas (estado: cerrada, published_at: pasado, closed_at: reciente)
        foreach ($programs->skip(2)->take(3) as $program) {
            $year = $academicYears->where('is_current', false)->first() ?? $academicYears->first();
            $destinations = fake()->randomElements($europeanCountries, fake()->numberBetween(3, 8));

            $callsData[] = [
                'program_id' => $program->id,
                'academic_year_id' => $year->id,
                'title' => "Convocatoria de movilidad {$program->name} - {$year->year}",
                'slug' => Str::slug("convocatoria-movilidad-{$program->slug}-{$year->year}-cerrada"),
                'type' => fake()->randomElement(['alumnado', 'personal']),
                'modality' => fake()->randomElement(['corta', 'larga']),
                'number_of_places' => fake()->numberBetween(8, 20),
                'destinations' => $destinations,
                'estimated_start_date' => now()->subMonths(3),
                'estimated_end_date' => now()->addMonths(3),
                'requirements' => "Requisitos para participar en esta convocatoria:\n\n".
                    "1. Estar matriculado/contratado en el centro educativo.\n".
                    "2. Tener un nivel mínimo de idioma B1 (según el destino).\n".
                    "3. Presentar documentación completa antes de la fecha límite.\n".
                    '4. Cumplir con los criterios específicos del programa.',
                'documentation' => "Documentación necesaria:\n\n".
                    "- Formulario de solicitud cumplimentado\n".
                    "- CV actualizado en formato Europass\n".
                    "- Carta de motivación\n".
                    "- Certificado de idiomas (nivel B1 mínimo)\n".
                    '- Certificado académico o laboral',
                'selection_criteria' => "Criterios de selección:\n\n".
                    "1. Expediente académico o profesional (40%)\n".
                    "2. Nivel de idiomas (30%)\n".
                    "3. Entrevista personal (20%)\n".
                    '4. Otros méritos (10%)',
                'scoring_table' => [
                    'expediente_academico' => 40,
                    'idioma' => 30,
                    'entrevista' => 20,
                    'otros_meritos' => 10,
                ],
                'status' => 'cerrada',
                'published_at' => now()->subMonths(fake()->numberBetween(3, 6)),
                'closed_at' => now()->subDays(fake()->numberBetween(1, 15)),
                'created_by' => $adminUser?->id,
                'updated_by' => $adminUser?->id,
            ];
        }

        // Crear convocatorias adicionales con variaciones
        foreach ($programs->take(2) as $program) {
            $year = $academicYears->where('is_current', true)->first() ?? $academicYears->first();
            $destinations = fake()->randomElements($europeanCountries, fake()->numberBetween(2, 5));

            // Convocatoria para alumnado
            $callsData[] = [
                'program_id' => $program->id,
                'academic_year_id' => $year->id,
                'title' => "Convocatoria movilidad alumnado {$program->name} - {$year->year}",
                'slug' => Str::slug("convocatoria-alumnado-{$program->slug}-{$year->year}"),
                'type' => 'alumnado',
                'modality' => fake()->randomElement(['corta', 'larga']),
                'number_of_places' => fake()->numberBetween(15, 30),
                'destinations' => $destinations,
                'estimated_start_date' => now()->addMonths(3),
                'estimated_end_date' => now()->addMonths(9),
                'requirements' => "Requisitos específicos para alumnado:\n\n".
                    "1. Estar matriculado en el centro educativo.\n".
                    "2. Tener un nivel mínimo de idioma B1.\n".
                    "3. No haber participado anteriormente en el mismo programa.\n".
                    '4. Presentar documentación completa.',
                'documentation' => "Documentación necesaria para alumnado:\n\n".
                    "- Formulario de solicitud\n".
                    "- Certificado académico\n".
                    "- Certificado de idiomas\n".
                    '- Carta de motivación',
                'selection_criteria' => "Criterios de selección para alumnado:\n\n".
                    "1. Expediente académico (50%)\n".
                    "2. Nivel de idiomas (30%)\n".
                    '3. Entrevista (20%)',
                'scoring_table' => [
                    'expediente_academico' => 50,
                    'idioma' => 30,
                    'entrevista' => 20,
                ],
                'status' => 'abierta',
                'published_at' => now()->subDays(fake()->numberBetween(10, 45)),
                'closed_at' => null,
                'created_by' => $adminUser?->id,
                'updated_by' => $adminUser?->id,
            ];

            // Convocatoria para personal
            $callsData[] = [
                'program_id' => $program->id,
                'academic_year_id' => $year->id,
                'title' => "Convocatoria movilidad personal {$program->name} - {$year->year}",
                'slug' => Str::slug("convocatoria-personal-{$program->slug}-{$year->year}"),
                'type' => 'personal',
                'modality' => fake()->randomElement(['corta', 'larga']),
                'number_of_places' => fake()->numberBetween(5, 15),
                'destinations' => $destinations,
                'estimated_start_date' => now()->addMonths(2),
                'estimated_end_date' => now()->addMonths(6),
                'requirements' => "Requisitos específicos para personal:\n\n".
                    "1. Estar contratado en el centro educativo.\n".
                    "2. Tener un nivel mínimo de idioma B2.\n".
                    "3. Justificar la necesidad formativa.\n".
                    '4. Presentar documentación completa.',
                'documentation' => "Documentación necesaria para personal:\n\n".
                    "- Formulario de solicitud\n".
                    "- CV actualizado\n".
                    "- Certificado de idiomas (B2 mínimo)\n".
                    '- Justificación de la movilidad',
                'selection_criteria' => "Criterios de selección para personal:\n\n".
                    "1. Experiencia profesional (40%)\n".
                    "2. Nivel de idiomas (30%)\n".
                    "3. Proyecto formativo (20%)\n".
                    '4. Entrevista (10%)',
                'scoring_table' => [
                    'experiencia_profesional' => 40,
                    'idioma' => 30,
                    'proyecto_formativo' => 20,
                    'entrevista' => 10,
                ],
                'status' => 'abierta',
                'published_at' => now()->subDays(fake()->numberBetween(5, 30)),
                'closed_at' => null,
                'created_by' => $adminUser?->id,
                'updated_by' => $adminUser?->id,
            ];
        }

        // Crear todas las convocatorias
        foreach ($callsData as $callData) {
            Call::updateOrCreate(
                ['slug' => $callData['slug']],
                $callData
            );
        }

        $this->command->info('Convocatorias creadas: '.count($callsData));
    }
}
