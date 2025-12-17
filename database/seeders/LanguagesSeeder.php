<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguagesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $languages = [
            [
                'code' => 'es',
                'name' => 'Español',
                'is_default' => true,
                'is_active' => true,
            ],
            [
                'code' => 'en',
                'name' => 'English',
                'is_default' => false,
                'is_active' => true,
            ],
        ];

        foreach ($languages as $languageData) {
            Language::firstOrCreate(
                ['code' => $languageData['code']],
                $languageData
            );
        }

        // Asegurar que solo un idioma sea el predeterminado
        $defaultLanguage = Language::where('is_default', true)->first();
        if ($defaultLanguage && $defaultLanguage->code !== 'es') {
            Language::where('code', 'es')->update(['is_default' => true]);
            $defaultLanguage->update(['is_default' => false]);
        }
    }
}
