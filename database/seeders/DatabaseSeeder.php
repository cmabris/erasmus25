<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            LanguagesSeeder::class,
            ProgramsSeeder::class,
            AcademicYearsSeeder::class,
            DocumentCategoriesSeeder::class,
            SettingsSeeder::class,
            RolesAndPermissionsSeeder::class,
            AdminUserSeeder::class,
            // Seeders de convocatorias (requieren Programs y AcademicYears)
            CallSeeder::class,
            CallPhaseSeeder::class,
            ResolutionSeeder::class,
            // Seeders de noticias (requieren Programs, AcademicYears y Users)
            NewsTagSeeder::class,
            NewsPostSeeder::class,
            // Seeders de documentos (requieren DocumentCategories, Programs, AcademicYears y Users)
            DocumentsSeeder::class,
            // Seeders de eventos (requieren Programs, Calls y Users)
            ErasmusEventSeeder::class,
        ]);
    }
}
