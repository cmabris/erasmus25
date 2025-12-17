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
            [
                'code' => 'KA1xx',
                'name' => 'Educación Escolar',
                'slug' => 'educacion-escolar',
                'description' => 'Programa Erasmus+ de Educación Escolar que promueve la movilidad del personal educativo y las asociaciones estratégicas entre centros escolares.',
                'is_active' => true,
                'order' => 1,
            ],
            [
                'code' => 'KA121-VET',
                'name' => 'Formación Profesional',
                'slug' => 'formacion-profesional',
                'description' => 'Programa Erasmus+ de Formación Profesional que facilita la movilidad de estudiantes y personal de FP, incluyendo FCT, prácticas, job shadowing y cursos de formación.',
                'is_active' => true,
                'order' => 2,
            ],
            [
                'code' => 'KA131-HED',
                'name' => 'Educación Superior',
                'slug' => 'educacion-superior',
                'description' => 'Programa Erasmus+ de Educación Superior que permite la movilidad de estudiantes y personal universitario para estudios, prácticas y formación.',
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($programs as $programData) {
            Program::firstOrCreate(
                ['code' => $programData['code']],
                $programData
            );
        }
    }
}
