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
        ]);
    }
}
