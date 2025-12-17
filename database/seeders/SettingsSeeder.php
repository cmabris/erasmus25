<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            // Configuración general
            [
                'key' => 'site_name',
                'value' => 'Erasmus+ Centro (Murcia)',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Nombre del sitio web',
            ],
            [
                'key' => 'site_description',
                'value' => 'Portal centralizado de información Erasmus+ para Educación Escolar, Formación Profesional y Educación Superior',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Descripción del sitio web',
            ],
            [
                'key' => 'contact_email',
                'value' => 'info@erasmus-murcia.es',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Email de contacto principal',
            ],
            [
                'key' => 'items_per_page',
                'value' => '15',
                'type' => 'integer',
                'group' => 'general',
                'description' => 'Número de elementos por página en listados',
            ],

            // Configuración de email
            [
                'key' => 'newsletter_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'email',
                'description' => 'Habilitar suscripción a newsletter',
            ],
            [
                'key' => 'notification_email',
                'value' => 'notificaciones@erasmus-murcia.es',
                'type' => 'string',
                'group' => 'email',
                'description' => 'Email para notificaciones del sistema',
            ],

            // Configuración RGPD
            [
                'key' => 'rgpd_enabled',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'rgpd',
                'description' => 'Habilitar funcionalidades RGPD',
            ],
            [
                'key' => 'cookie_consent_required',
                'value' => '1',
                'type' => 'boolean',
                'group' => 'rgpd',
                'description' => 'Requerir consentimiento de cookies',
            ],

            // Configuración de medios
            [
                'key' => 'max_file_size',
                'value' => '10485760',
                'type' => 'integer',
                'group' => 'media',
                'description' => 'Tamaño máximo de archivo en bytes (10MB)',
            ],
            [
                'key' => 'allowed_file_types',
                'value' => json_encode(['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png']),
                'type' => 'json',
                'group' => 'media',
                'description' => 'Tipos de archivo permitidos',
            ],

            // Configuración SEO
            [
                'key' => 'default_meta_title',
                'value' => 'Erasmus+ Centro (Murcia) - Portal de Información Erasmus+',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Título meta por defecto',
            ],
            [
                'key' => 'default_meta_description',
                'value' => 'Portal centralizado de información Erasmus+ para Educación Escolar, Formación Profesional y Educación Superior',
                'type' => 'string',
                'group' => 'seo',
                'description' => 'Descripción meta por defecto',
            ],
        ];

        foreach ($settings as $settingData) {
            Setting::firstOrCreate(
                ['key' => $settingData['key']],
                $settingData
            );
        }
    }
}
