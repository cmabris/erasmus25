<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use Illuminate\Database\Seeder;

class DocumentCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Convocatorias',
                'slug' => 'convocatorias',
                'description' => 'Documentos relacionados con convocatorias de programas Erasmus+.',
                'order' => 1,
            ],
            [
                'name' => 'Modelos',
                'slug' => 'modelos',
                'description' => 'Modelos y plantillas de documentos oficiales.',
                'order' => 2,
            ],
            [
                'name' => 'Seguros',
                'slug' => 'seguros',
                'description' => 'Documentación relacionada con seguros y coberturas.',
                'order' => 3,
            ],
            [
                'name' => 'Consentimientos',
                'slug' => 'consentimientos',
                'description' => 'Formularios de consentimiento y autorizaciones.',
                'order' => 4,
            ],
            [
                'name' => 'Guías',
                'slug' => 'guias',
                'description' => 'Guías y manuales de procedimientos.',
                'order' => 5,
            ],
            [
                'name' => 'FAQ',
                'slug' => 'faq',
                'description' => 'Preguntas frecuentes y respuestas.',
                'order' => 6,
            ],
            [
                'name' => 'Otros',
                'slug' => 'otros',
                'description' => 'Otros documentos diversos.',
                'order' => 7,
            ],
        ];

        foreach ($categories as $categoryData) {
            DocumentCategory::firstOrCreate(
                ['slug' => $categoryData['slug']],
                $categoryData
            );
        }
    }
}
