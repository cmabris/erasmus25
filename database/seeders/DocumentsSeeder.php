<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $categories = DocumentCategory::all();
        $programs = Program::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first();
        $users = User::where('id', '!=', $adminUser?->id)->get();

        if ($categories->isEmpty()) {
            $this->command->warn('No hay categorías disponibles. Ejecuta primero DocumentCategoriesSeeder.');

            return;
        }

        if ($programs->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No hay programas o años académicos disponibles. Ejecuta primero ProgramsSeeder y AcademicYearsSeeder.');

            return;
        }

        $documentTypes = ['convocatoria', 'modelo', 'seguro', 'consentimiento', 'guia', 'faq', 'otro'];

        // Documentos destacados por categoría
        $documentsData = [
            // Convocatorias
            [
                'title' => 'Convocatoria Erasmus+ Educación Escolar 2024-2025',
                'description' => 'Convocatoria oficial para proyectos de movilidad de alumnado y personal en el marco del programa Erasmus+ Educación Escolar.',
                'document_type' => 'convocatoria',
                'category_slug' => 'convocatorias',
                'version' => '1.0',
                'download_count' => fake()->numberBetween(50, 200),
            ],
            [
                'title' => 'Convocatoria KA121-VET Formación Profesional',
                'description' => 'Convocatoria para movilidades de estudiantes de Formación Profesional y personal docente.',
                'document_type' => 'convocatoria',
                'category_slug' => 'convocatorias',
                'version' => '2.1',
                'download_count' => fake()->numberBetween(30, 150),
            ],
            [
                'title' => 'Convocatoria KA131-HED Educación Superior',
                'description' => 'Convocatoria para movilidades de estudiantes y personal de Educación Superior.',
                'document_type' => 'convocatoria',
                'category_slug' => 'convocatorias',
                'version' => '1.5',
                'download_count' => fake()->numberBetween(40, 180),
            ],

            // Modelos
            [
                'title' => 'Modelo de Carta de Motivación',
                'description' => 'Plantilla oficial para redactar la carta de motivación requerida en las solicitudes de movilidad.',
                'document_type' => 'modelo',
                'category_slug' => 'modelos',
                'version' => '3.0',
                'download_count' => fake()->numberBetween(100, 300),
            ],
            [
                'title' => 'Modelo de Acuerdo de Aprendizaje (Learning Agreement)',
                'description' => 'Documento oficial para establecer el plan de estudios durante la movilidad.',
                'document_type' => 'modelo',
                'category_slug' => 'modelos',
                'version' => '2024',
                'download_count' => fake()->numberBetween(80, 250),
            ],
            [
                'title' => 'Modelo de Informe Final de Movilidad',
                'description' => 'Plantilla para documentar la experiencia y resultados de la movilidad Erasmus+.',
                'document_type' => 'modelo',
                'category_slug' => 'modelos',
                'version' => '2.2',
                'download_count' => fake()->numberBetween(60, 200),
            ],

            // Seguros
            [
                'title' => 'Guía de Seguro de Viaje Erasmus+',
                'description' => 'Información completa sobre la cobertura de seguro requerida para movilidades Erasmus+.',
                'document_type' => 'seguro',
                'category_slug' => 'seguros',
                'version' => '1.0',
                'download_count' => fake()->numberBetween(70, 220),
            ],
            [
                'title' => 'Tarjeta Sanitaria Europea - Información',
                'description' => 'Guía sobre cómo obtener y utilizar la Tarjeta Sanitaria Europea durante la movilidad.',
                'document_type' => 'seguro',
                'category_slug' => 'seguros',
                'version' => '1.1',
                'download_count' => fake()->numberBetween(50, 180),
            ],

            // Consentimientos
            [
                'title' => 'Formulario de Consentimiento para Uso de Imágenes',
                'description' => 'Documento para obtener el consentimiento de participantes para el uso de imágenes y material multimedia.',
                'document_type' => 'consentimiento',
                'category_slug' => 'consentimientos',
                'version' => '1.0',
                'download_count' => fake()->numberBetween(40, 150),
            ],
            [
                'title' => 'Autorización RGPD para Tratamiento de Datos',
                'description' => 'Formulario de consentimiento para el tratamiento de datos personales según RGPD.',
                'document_type' => 'consentimiento',
                'category_slug' => 'consentimientos',
                'version' => '2.0',
                'download_count' => fake()->numberBetween(35, 120),
            ],

            // Guías
            [
                'title' => 'Guía Completa de Movilidad Erasmus+',
                'description' => 'Manual completo con toda la información necesaria para participar en programas de movilidad Erasmus+.',
                'document_type' => 'guia',
                'category_slug' => 'guias',
                'version' => '2024',
                'download_count' => fake()->numberBetween(150, 400),
            ],
            [
                'title' => 'Guía de Preparación para la Movilidad',
                'description' => 'Checklist y recomendaciones para preparar tu experiencia de movilidad internacional.',
                'document_type' => 'guia',
                'category_slug' => 'guias',
                'version' => '1.3',
                'download_count' => fake()->numberBetween(90, 280),
            ],
            [
                'title' => 'Guía de Destinos Erasmus+',
                'description' => 'Información sobre destinos disponibles, requisitos y oportunidades en diferentes países europeos.',
                'document_type' => 'guia',
                'category_slug' => 'guias',
                'version' => '2024-2025',
                'download_count' => fake()->numberBetween(120, 350),
            ],

            // FAQ
            [
                'title' => 'Preguntas Frecuentes sobre Erasmus+',
                'description' => 'Respuestas a las preguntas más comunes sobre programas, requisitos y procedimientos Erasmus+.',
                'document_type' => 'faq',
                'category_slug' => 'faq',
                'version' => '1.0',
                'download_count' => fake()->numberBetween(200, 500),
            ],
            [
                'title' => 'FAQ - Movilidad de Estudiantes',
                'description' => 'Preguntas frecuentes específicas sobre movilidad de estudiantes en programas Erasmus+.',
                'document_type' => 'faq',
                'category_slug' => 'faq',
                'version' => '1.1',
                'download_count' => fake()->numberBetween(150, 400),
            ],

            // Otros
            [
                'title' => 'Calendario de Fechas Importantes 2024-2025',
                'description' => 'Cronograma con todas las fechas importantes para solicitudes, plazos y eventos relacionados con Erasmus+.',
                'document_type' => 'otro',
                'category_slug' => 'otros',
                'version' => '2024-2025',
                'download_count' => fake()->numberBetween(80, 250),
            ],
        ];

        // Crear documentos principales
        foreach ($documentsData as $docData) {
            $category = $categories->firstWhere('slug', $docData['category_slug']);
            if (! $category) {
                continue;
            }

            $program = fake()->boolean(70) ? $programs->random() : null;
            $academicYear = fake()->boolean(60) ? $academicYears->random() : null;
            $creator = $users->isNotEmpty() ? $users->random() : $adminUser;

            $document = Document::create([
                'category_id' => $category->id,
                'program_id' => $program?->id,
                'academic_year_id' => $academicYear?->id,
                'title' => $docData['title'],
                'slug' => Str::slug($docData['title']),
                'description' => $docData['description'],
                'document_type' => $docData['document_type'],
                'version' => $docData['version'],
                'is_active' => true,
                'download_count' => $docData['download_count'],
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Asociar archivo de prueba (crear archivo temporal)
            $this->attachSampleFile($document, $docData['document_type']);
        }

        // Crear documentos adicionales variados
        for ($i = 0; $i < 25; $i++) {
            $category = $categories->random();
            $program = fake()->boolean(60) ? $programs->random() : null;
            $academicYear = fake()->boolean(50) ? $academicYears->random() : null;
            $documentType = fake()->randomElement($documentTypes);
            $creator = $users->isNotEmpty() ? $users->random() : $adminUser;

            $titles = [
                'Documento de '.$category->name.' - '.fake()->words(2, true),
                'Recurso '.fake()->word().' para '.($program ? $program->name : 'Erasmus+'),
                'Información sobre '.fake()->words(2, true),
                'Formulario de '.fake()->word(),
                'Guía de '.fake()->words(2, true),
            ];

            $document = Document::create([
                'category_id' => $category->id,
                'program_id' => $program?->id,
                'academic_year_id' => $academicYear?->id,
                'title' => fake()->randomElement($titles),
                'slug' => Str::slug(fake()->sentence(3)),
                'description' => fake()->paragraph(2),
                'document_type' => $documentType,
                'version' => fake()->boolean(40) ? fake()->randomElement(['1.0', '1.1', '2.0', '2024']) : null,
                'is_active' => true,
                'download_count' => fake()->numberBetween(0, 100),
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);

            // Asociar archivo de prueba (solo algunos)
            if (fake()->boolean(70)) {
                $this->attachSampleFile($document, $documentType);
            }
        }

        // Crear algunos documentos inactivos (no se mostrarán en público)
        for ($i = 0; $i < 5; $i++) {
            $category = $categories->random();
            $program = fake()->boolean(50) ? $programs->random() : null;
            $documentType = fake()->randomElement($documentTypes);
            $creator = $users->isNotEmpty() ? $users->random() : $adminUser;

            Document::create([
                'category_id' => $category->id,
                'program_id' => $program?->id,
                'academic_year_id' => null,
                'title' => 'Documento Archivado - '.fake()->words(3, true),
                'slug' => Str::slug('documento-archivado-'.fake()->word()),
                'description' => fake()->paragraph(),
                'document_type' => $documentType,
                'version' => null,
                'is_active' => false,
                'download_count' => fake()->numberBetween(0, 50),
                'created_by' => $creator?->id,
                'updated_by' => $creator?->id,
            ]);
        }

        // Nota: MediaConsent está diseñado para archivos multimedia (imágenes/videos/audios)
        // que contienen personas, no para documentos PDF. Los MediaConsent deberían crearse
        // cuando se suban imágenes/videos con personas (por ejemplo, en NewsPost con imágenes).
        // Por lo tanto, no creamos MediaConsent en este seeder de documentos.

        $this->command->info('Documentos creados exitosamente.');
    }

    /**
     * Attach a sample file to a document.
     */
    private function attachSampleFile(Document $document, string $documentType): void
    {
        try {
            // Crear contenido de ejemplo según el tipo
            $content = match ($documentType) {
                'convocatoria' => $this->generateConvocatoriaContent(),
                'modelo' => $this->generateModeloContent(),
                'seguro' => $this->generateSeguroContent(),
                'consentimiento' => $this->generateConsentimientoContent(),
                'guia' => $this->generateGuiaContent(),
                'faq' => $this->generateFaqContent(),
                default => $this->generateGenericContent(),
            };

            // Crear archivo temporal
            $fileName = Str::slug($document->title).'.pdf';
            $tempPath = storage_path('app/temp/'.$fileName);

            // Asegurar que el directorio existe
            if (! is_dir(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }

            // Escribir contenido (simulando PDF con texto plano)
            file_put_contents($tempPath, $content);

            // Adjuntar archivo usando Media Library
            $document->addMedia($tempPath)
                ->usingName($document->title)
                ->usingFileName($fileName)
                ->toMediaCollection('file');

            // Limpiar archivo temporal
            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        } catch (\Exception $e) {
            $this->command->warn("No se pudo adjuntar archivo a documento {$document->id}: {$e->getMessage()}");
        }
    }

    /**
     * Generate sample content for different document types.
     */
    private function generateConvocatoriaContent(): string
    {
        return "CONVOCATORIA ERASMUS+\n\n".
               "Este es un documento de ejemplo de convocatoria.\n\n".
               "Información sobre requisitos, plazos y procedimientos.\n\n".
               "Fecha: ".now()->format('d/m/Y');
    }

    private function generateModeloContent(): string
    {
        return "MODELO DE DOCUMENTO\n\n".
               "Este es un modelo o plantilla de ejemplo.\n\n".
               "Complete los campos necesarios según sus necesidades.\n\n".
               "Versión: 1.0";
    }

    private function generateSeguroContent(): string
    {
        return "INFORMACIÓN DE SEGURO\n\n".
               "Documentación sobre cobertura de seguros.\n\n".
               "Información sobre tarifas, coberturas y procedimientos.\n\n".
               "Actualizado: ".now()->format('d/m/Y');
    }

    private function generateConsentimientoContent(): string
    {
        return "FORMULARIO DE CONSENTIMIENTO\n\n".
               "Autorización para el uso de imágenes y datos personales.\n\n".
               "Complete y firme este documento según las instrucciones.\n\n".
               "Fecha: ".now()->format('d/m/Y');
    }

    private function generateGuiaContent(): string
    {
        return "GUÍA ERASMUS+\n\n".
               "Manual completo con información detallada.\n\n".
               "Incluye procedimientos, recomendaciones y recursos útiles.\n\n".
               "Edición: ".now()->year;
    }

    private function generateFaqContent(): string
    {
        return "PREGUNTAS FRECUENTES\n\n".
               "Respuestas a las preguntas más comunes.\n\n".
               "Actualizado regularmente con nueva información.\n\n".
               "Última actualización: ".now()->format('d/m/Y');
    }

    private function generateGenericContent(): string
    {
        return "DOCUMENTO ERASMUS+\n\n".
               "Información y recursos relacionados con programas Erasmus+.\n\n".
               "Fecha: ".now()->format('d/m/Y');
    }
}

