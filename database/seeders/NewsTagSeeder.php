<?php

namespace Database\Seeders;

use App\Models\NewsTag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Etiquetas básicas comunes para el sitio web (disponibles en desarrollo y producción)
        $basicTags = [
            'Noticias',
            'Eventos',
            'Convocatorias',
            'Erasmus+',
            'Movilidad',
            'Formación',
        ];

        // Etiquetas adicionales para desarrollo (más específicas)
        $developmentTags = [
            'Movilidad Estudiantil',
            'Movilidad Personal',
            'Formación Profesional',
            'Educación Superior',
            'FCT',
            'Job Shadowing',
            'Intercambio',
            'Curso de Formación',
            'Experiencia Internacional',
            'Europa',
            'KA1',
            'KA2',
            'KA3',
            'Prácticas',
            'Estudios',
            'Desarrollo Profesional',
            'Idiomas',
            'Cultura',
            'Innovación',
            'Sostenibilidad',
            'Inclusión',
            'Digital',
            'Verde',
            'Testimonio',
            'Éxito',
            'Colaboración',
            'Networking',
            'Internacionalización',
            'Buenas Prácticas',
        ];

        // Combinar todas las etiquetas
        $tags = array_merge($basicTags, $developmentTags);

        foreach ($tags as $tagName) {
            NewsTag::updateOrCreate(
                ['slug' => Str::slug($tagName)],
                ['name' => $tagName]
            );
        }

        $this->command->info('Etiquetas de noticias creadas: '.count($tags));
    }
}
