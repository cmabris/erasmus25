<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class DashboardDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = Program::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        $categories = DocumentCategory::all();
        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first() ?? User::first();

        if ($programs->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No hay programas o años académicos disponibles. Ejecuta primero ProgramsSeeder y AcademicYearsSeeder.');

            return;
        }

        $now = Carbon::now();

        // Generar datos para los últimos 6 meses
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();

            // Convocatorias creadas este mes (2-5 por mes)
            $callsCount = rand(2, 5);
            for ($j = 0; $j < $callsCount; $j++) {
                $createdAt = Carbon::parse(fake()->dateTimeBetween($monthStart, $monthEnd));
                $status = fake()->randomElement(['borrador', 'abierta', 'cerrada']);
                $publishedAt = ($status === 'abierta' || $status === 'cerrada') ? Carbon::parse(fake()->dateTimeBetween($createdAt, $monthEnd)) : null;
                $closedAt = ($status === 'cerrada' && $publishedAt) ? Carbon::parse(fake()->dateTimeBetween($publishedAt, $monthEnd)) : null;

                $program = $programs->random();
                $academicYear = $academicYears->random();
                $title = "Convocatoria de movilidad {$program->name} - {$academicYear->year}";

                Call::create([
                    'program_id' => $program->id,
                    'academic_year_id' => $academicYear->id,
                    'title' => $title,
                    'slug' => \Illuminate\Support\Str::slug($title).'-'.uniqid(),
                    'type' => fake()->randomElement(['alumnado', 'personal']),
                    'modality' => fake()->randomElement(['corta', 'larga']),
                    'number_of_places' => fake()->numberBetween(5, 30),
                    'destinations' => fake()->randomElements(['Alemania', 'Francia', 'Italia', 'Portugal', 'Polonia', 'Países Bajos'], fake()->numberBetween(3, 6)),
                    'estimated_start_date' => fake()->dateTimeBetween('+1 month', '+6 months'),
                    'estimated_end_date' => fake()->dateTimeBetween('+7 months', '+12 months'),
                    'requirements' => fake()->paragraphs(3, true),
                    'documentation' => fake()->paragraphs(2, true),
                    'selection_criteria' => fake()->paragraphs(2, true),
                    'scoring_table' => [
                        'expediente_academico' => 40,
                        'idioma' => 30,
                        'entrevista' => 20,
                        'otros_meritos' => 10,
                    ],
                    'status' => $status,
                    'published_at' => $publishedAt,
                    'closed_at' => $closedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                    'created_by' => $adminUser->id,
                    'updated_by' => $adminUser->id,
                ]);
            }

            // Noticias publicadas este mes (3-8 por mes)
            $newsCount = rand(3, 8);
            for ($j = 0; $j < $newsCount; $j++) {
                $publishedAt = Carbon::parse(fake()->dateTimeBetween($monthStart, $monthEnd));
                $createdAt = Carbon::parse(fake()->dateTimeBetween($monthStart->copy()->subDays(7), $publishedAt));
                $program = fake()->boolean(70) ? $programs->random() : null;
                $academicYear = $academicYears->random();
                $title = fake()->sentence(6);

                NewsPost::create([
                    'program_id' => $program?->id,
                    'academic_year_id' => $academicYear->id,
                    'title' => $title,
                    'slug' => \Illuminate\Support\Str::slug($title).'-'.uniqid(),
                    'excerpt' => fake()->paragraph(2),
                    'content' => fake()->paragraphs(5, true),
                    'country' => fake()->optional(0.7)->randomElement(['Alemania', 'Francia', 'Italia', 'Portugal', 'Polonia']),
                    'city' => fake()->optional(0.5)->city(),
                    'host_entity' => fake()->optional(0.6)->company(),
                    'mobility_type' => fake()->optional(0.7)->randomElement(['alumnado', 'personal']),
                    'mobility_category' => fake()->optional(0.7)->randomElement(['FCT', 'job_shadowing', 'intercambio', 'curso']),
                    'status' => 'publicado',
                    'published_at' => $publishedAt,
                    'created_at' => $createdAt,
                    'updated_at' => $publishedAt,
                    'author_id' => $adminUser->id,
                    'reviewed_by' => $adminUser->id,
                    'reviewed_at' => $publishedAt->copy()->subDays(1),
                ]);
            }

            // Documentos creados este mes (4-10 por mes)
            if ($categories->isNotEmpty()) {
                $documentsCount = rand(4, 10);
                for ($j = 0; $j < $documentsCount; $j++) {
                    $createdAt = Carbon::parse(fake()->dateTimeBetween($monthStart, $monthEnd));
                    $category = $categories->random();
                    $program = fake()->boolean(60) ? $programs->random() : null;
                    $academicYear = fake()->boolean(50) ? $academicYears->random() : null;
                    $title = fake()->sentence(4);

                    Document::create([
                        'category_id' => $category->id,
                        'program_id' => $program?->id,
                        'academic_year_id' => $academicYear?->id,
                        'title' => $title,
                        'slug' => \Illuminate\Support\Str::slug($title).'-'.uniqid(),
                        'description' => fake()->optional(0.8)->paragraph(),
                        'document_type' => fake()->randomElement(['convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro']),
                        'version' => fake()->optional(0.3)->randomElement(['1.0', '2.0', '1.1']),
                        'download_count' => fake()->numberBetween(0, 100),
                        'is_active' => true,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                        'created_by' => $adminUser->id,
                        'updated_by' => $adminUser->id,
                    ]);
                }
            }
        }

        $this->command->info('Datos del dashboard generados correctamente para los últimos 6 meses.');
    }
}
