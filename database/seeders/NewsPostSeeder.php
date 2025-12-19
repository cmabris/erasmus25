<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsPostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $programs = Program::where('is_active', true)->get();
        $academicYears = AcademicYear::all();
        $tags = NewsTag::all();
        $adminUser = User::where('email', 'admin@erasmus-murcia.es')->first();
        $users = User::where('id', '!=', $adminUser?->id)->get();

        if ($programs->isEmpty() || $academicYears->isEmpty()) {
            $this->command->warn('No hay programas o años académicos disponibles. Ejecuta primero ProgramsSeeder y AcademicYearsSeeder.');

            return;
        }

        if ($tags->isEmpty()) {
            $this->command->warn('No hay etiquetas disponibles. Ejecuta primero NewsTagSeeder.');

            return;
        }

        // Países europeos para las noticias
        $europeanCountries = [
            'Alemania', 'Austria', 'Bélgica', 'Bulgaria', 'Chipre', 'Croacia',
            'Dinamarca', 'Eslovaquia', 'Eslovenia', 'España', 'Estonia', 'Finlandia',
            'Francia', 'Grecia', 'Hungría', 'Irlanda', 'Italia', 'Letonia',
            'Lituania', 'Luxemburgo', 'Malta', 'Países Bajos', 'Polonia', 'Portugal',
            'República Checa', 'Rumanía', 'Suecia',
        ];

        $cities = [
            'Berlín', 'Viena', 'Bruselas', 'Sofía', 'Nicosia', 'Zagreb',
            'Copenhague', 'Bratislava', 'Liubliana', 'Madrid', 'Tallin', 'Helsinki',
            'París', 'Atenas', 'Budapest', 'Dublín', 'Roma', 'Riga',
            'Vilna', 'Luxemburgo', 'La Valeta', 'Ámsterdam', 'Varsovia', 'Lisboa',
            'Praga', 'Bucarest', 'Estocolmo',
        ];

        $newsData = [];

        // Noticias destacadas recientes (publicadas)
        $featuredTitles = [
            'Experiencia Erasmus+ en Alemania: Una aventura transformadora',
            'Movilidad de personal docente en Finlandia: Aprendiendo del mejor sistema educativo',
            'Estudiantes de FCT comparten su experiencia en empresas europeas',
            'Nuevo proyecto KA2: Colaboración internacional para la innovación educativa',
            'Testimonio: Cómo Erasmus+ cambió mi perspectiva profesional',
            'Formación en sostenibilidad: Nuestro centro participa en proyecto verde',
            'Intercambio cultural exitoso con centro educativo en Francia',
            'Desarrollo de competencias digitales a través de movilidad',
        ];

        foreach ($featuredTitles as $index => $title) {
            $program = $programs->random();
            $year = $academicYears->where('is_current', true)->first() ?? $academicYears->random();
            $country = fake()->randomElement($europeanCountries);
            $city = fake()->randomElement($cities);
            $author = $users->isNotEmpty() ? $users->random() : $adminUser;
            $publishedAt = now()->subDays(fake()->numberBetween(1, 60));

            $newsData[] = [
                'program_id' => $program->id,
                'academic_year_id' => $year->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => fake()->paragraph(3),
                'content' => $this->generateContent($title, $country, $city),
                'country' => $country,
                'city' => $city,
                'host_entity' => fake()->company().' / '.fake()->company(),
                'mobility_type' => fake()->randomElement(['alumnado', 'personal']),
                'mobility_category' => fake()->randomElement(['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro']),
                'status' => 'publicado',
                'published_at' => $publishedAt,
                'author_id' => $author?->id,
                'reviewed_by' => $adminUser?->id,
                'reviewed_at' => $publishedAt->copy()->subDays(1),
            ];
        }

        // Noticias variadas adicionales
        $additionalTitles = [
            'Resultados del programa de movilidad del curso anterior',
            'Convocatoria abierta: Últimas plazas disponibles',
            'Reunión informativa sobre oportunidades Erasmus+',
            'Alumnos comparten sus experiencias en el extranjero',
            'Formación del profesorado en metodologías innovadoras',
            'Proyecto de inclusión: Erasmus+ para todos',
            'Colaboración con centros educativos europeos',
            'Celebración del Día de Europa en nuestro centro',
            'Nuevas oportunidades de movilidad para el próximo curso',
            'Testimonios de éxito: Historias de movilidad',
            'Desarrollo de competencias clave a través de Erasmus+',
            'Intercambio de buenas prácticas educativas',
            'Movilidad sostenible: Nuestro compromiso con el medio ambiente',
            'Experiencias de aprendizaje en entornos internacionales',
            'Networking y colaboración en proyectos europeos',
        ];

        foreach ($additionalTitles as $title) {
            $program = fake()->boolean(70) ? $programs->random() : null;
            $year = $academicYears->random();
            $country = fake()->optional(0.7)->randomElement($europeanCountries);
            $city = $country ? fake()->randomElement($cities) : null;
            $author = $users->isNotEmpty() ? $users->random() : $adminUser;
            $publishedAt = now()->subDays(fake()->numberBetween(1, 180));

            $newsData[] = [
                'program_id' => $program?->id,
                'academic_year_id' => $year->id,
                'title' => $title,
                'slug' => Str::slug($title),
                'excerpt' => fake()->paragraph(2),
                'content' => $this->generateContent($title, $country, $city),
                'country' => $country,
                'city' => $city,
                'host_entity' => fake()->optional(0.6)->company(),
                'mobility_type' => fake()->optional(0.7)->randomElement(['alumnado', 'personal']),
                'mobility_category' => fake()->optional(0.7)->randomElement(['FCT', 'job_shadowing', 'intercambio', 'curso', 'otro']),
                'status' => 'publicado',
                'published_at' => $publishedAt,
                'author_id' => $author?->id,
                'reviewed_by' => $adminUser?->id,
                'reviewed_at' => $publishedAt->copy()->subDays(1),
            ];
        }

        // Crear noticias
        $createdNews = [];
        foreach ($newsData as $newsItem) {
            $news = NewsPost::updateOrCreate(
                ['slug' => $newsItem['slug']],
                $newsItem
            );
            $createdNews[] = $news;

            // Asignar etiquetas aleatorias (2-5 etiquetas por noticia)
            $newsTags = $tags->random(fake()->numberBetween(2, 5));
            $news->tags()->sync($newsTags->pluck('id'));
        }

        // Añadir imágenes destacadas a algunas noticias (simuladas con URLs de placeholder)
        // En producción, se usarían archivos reales
        $this->command->info('Noticias creadas: '.count($createdNews));
        $this->command->info('Nota: Para añadir imágenes destacadas, usa Media Library después de crear las noticias.');
    }

    /**
     * Generate realistic content for a news post.
     */
    private function generateContent(string $title, ?string $country, ?string $city): string
    {
        $location = $country && $city ? " en {$city}, {$country}" : ($country ? " en {$country}" : '');

        $paragraphs = [
            fake()->paragraph(4),
            fake()->paragraph(5),
        ];

        if ($location) {
            $paragraphs[] = "La experiencia{$location} ha sido enriquecedora tanto a nivel personal como profesional. Los participantes han tenido la oportunidad de sumergirse en una cultura diferente, mejorar sus competencias lingüísticas y desarrollar nuevas habilidades.";
        }

        $paragraphs[] = fake()->paragraph(4);
        $paragraphs[] = 'Este tipo de movilidades son fundamentales para el desarrollo de competencias clave en el siglo XXI, fomentando la ciudadanía europea y el entendimiento mutuo entre diferentes culturas.';

        if (fake()->boolean(60)) {
            $paragraphs[] = 'Los participantes destacan la importancia de estas experiencias para su desarrollo profesional y personal, y animan a otros a participar en futuras convocatorias.';
        }

        return implode("\n\n", $paragraphs);
    }
}
