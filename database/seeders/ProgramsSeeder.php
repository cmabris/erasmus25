<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $programs = [
            // KA1 - Movilidad de las personas
            [
                'code' => 'KA121-SCH',
                'name' => 'Movilidad Educación Escolar',
                'slug' => 'movilidad-educacion-escolar',
                'description' => 'Programa de movilidad para centros educativos de enseñanza primaria y secundaria. Permite al personal docente y no docente realizar estancias de observación (job shadowing), cursos de formación y actividades de enseñanza en centros educativos de otros países europeos. El objetivo es mejorar las competencias profesionales, conocer nuevas metodologías pedagógicas y establecer redes de colaboración internacional.',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'code' => 'KA121-VET',
                'name' => 'Movilidad Formación Profesional',
                'slug' => 'movilidad-formacion-profesional',
                'description' => 'Programa de movilidad para estudiantes y personal de Formación Profesional. Los estudiantes pueden realizar prácticas en empresas europeas (FCT internacional), mientras que el profesorado puede participar en estancias de observación, cursos de formación especializados y actividades de enseñanza. Incluye movilidades de corta y larga duración, adaptadas a las necesidades formativas de cada ciclo.',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'code' => 'KA131-HED',
                'name' => 'Movilidad Educación Superior',
                'slug' => 'movilidad-educacion-superior',
                'description' => 'Programa de movilidad para estudiantes universitarios y personal de instituciones de educación superior. Los estudiantes pueden cursar un periodo de estudios o realizar prácticas en universidades y empresas de países del programa. El personal docente e investigador puede impartir docencia o recibir formación. Duración típica de 3 a 12 meses para estudiantes.',
                'is_active' => true,
                'order' => 3,
            ],
            [
                'code' => 'KA122-ADU',
                'name' => 'Movilidad Educación de Adultos',
                'slug' => 'movilidad-educacion-adultos',
                'description' => 'Programa de movilidad para organizaciones de educación de adultos. Permite al personal educativo participar en cursos de formación, estancias de observación y actividades de enseñanza en otros países europeos. También incluye movilidades para alumnado adulto que desee mejorar sus competencias básicas y transversales a través de experiencias de aprendizaje internacional.',
                'is_active' => true,
                'order' => 4,
            ],

            // KA2 - Cooperación entre organizaciones
            [
                'code' => 'KA220-SCH',
                'name' => 'Asociaciones de Cooperación Escolar',
                'slug' => 'asociaciones-cooperacion-escolar',
                'description' => 'Proyectos de colaboración entre centros educativos de diferentes países europeos. Las asociaciones de cooperación permiten desarrollar proyectos conjuntos sobre temáticas innovadoras: metodologías activas, educación digital, inclusión educativa, sostenibilidad medioambiental, patrimonio cultural, etc. Duración de 12 a 36 meses con financiación para reuniones, actividades y producción de recursos educativos.',
                'is_active' => true,
                'order' => 5,
            ],
            [
                'code' => 'KA220-VET',
                'name' => 'Asociaciones de Cooperación FP',
                'slug' => 'asociaciones-cooperacion-fp',
                'description' => 'Proyectos de colaboración entre centros de Formación Profesional, empresas y organizaciones de diferentes países. Permite desarrollar materiales formativos innovadores, metodologías de enseñanza adaptadas al mundo laboral, herramientas de evaluación de competencias y recursos para la digitalización de la FP. Financiación para reuniones transnacionales, desarrollo de productos y difusión de resultados.',
                'is_active' => true,
                'order' => 6,
            ],
            [
                'code' => 'KA210-SCH',
                'name' => 'Asociaciones a Pequeña Escala (Escolar)',
                'slug' => 'asociaciones-pequena-escala-escolar',
                'description' => 'Proyectos de cooperación simplificados para centros educativos con menos experiencia en programas europeos. Formato más accesible con menor carga administrativa, presupuestos fijos (30.000€ o 60.000€) y requisitos simplificados. Ideal para primeras experiencias internacionales o proyectos de alcance limitado. Duración de 6 a 24 meses.',
                'is_active' => true,
                'order' => 7,
            ],

            // Programa Jean Monnet
            [
                'code' => 'JM-HEI',
                'name' => 'Jean Monnet - Educación Superior',
                'slug' => 'jean-monnet-educacion-superior',
                'description' => 'Acciones Jean Monnet para promover la excelencia en la enseñanza y la investigación sobre la Unión Europea. Incluye módulos de enseñanza sobre estudios de la UE, cátedras Jean Monnet, centros de excelencia y redes de universidades. Dirigido a instituciones de educación superior que deseen profundizar en temas de integración europea, políticas de la UE y valores europeos.',
                'is_active' => true,
                'order' => 8,
            ],

            // DiscoverEU
            [
                'code' => 'DISCOVER-EU',
                'name' => 'DiscoverEU',
                'slug' => 'discover-eu',
                'description' => 'Iniciativa que ofrece a los jóvenes de 18 años la oportunidad de descubrir Europa viajando principalmente en tren. Los participantes seleccionados reciben un pase de viaje gratuito para explorar Europa durante un máximo de 30 días. El programa fomenta el sentimiento de pertenencia a la Unión Europea, el descubrimiento de su diversidad cultural y patrimonio, y el desarrollo de competencias para la vida.',
                'is_active' => false, // No gestionado directamente por centros educativos
                'order' => 9,
            ],

            // Programa histórico (ejemplo de programa inactivo)
            [
                'code' => 'KA1-2014',
                'name' => 'Movilidad 2014-2020 (Histórico)',
                'slug' => 'movilidad-2014-2020-historico',
                'description' => 'Programa de movilidad del periodo 2014-2020 de Erasmus+. Este programa ya ha finalizado y se mantiene únicamente con fines de archivo histórico y consulta de convocatorias anteriores. Las nuevas solicitudes deben realizarse en los programas del periodo 2021-2027.',
                'is_active' => false,
                'order' => 99,
            ],
        ];

        foreach ($programs as $programData) {
            Program::updateOrCreate(
                ['code' => $programData['code']],
                $programData
            );
        }
    }
}
