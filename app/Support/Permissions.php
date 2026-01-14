<?php

namespace App\Support;

/**
 * Constantes para los permisos del sistema.
 *
 * Los permisos est?n organizados por m?dulo:
 * - programs: Gesti?n de programas Erasmus+
 * - calls: Gesti?n de convocatorias
 * - news: Gesti?n de noticias
 * - documents: Gesti?n de documentos
 * - events: Gesti?n de eventos
 * - users: Gesti?n de usuarios
 * - newsletter: Gesti?n de suscripciones newsletter
 *
 * Cada m?dulo tiene permisos espec?ficos:
 * - view: Ver listados y detalles
 * - create: Crear nuevos registros
 * - edit: Editar registros existentes
 * - delete: Eliminar registros
 * - publish: Publicar contenido (solo para calls y news)
 * - *: Todos los permisos del m?dulo
 */
class Permissions
{
    // Permisos de Programas
    public const PROGRAMS_VIEW = 'programs.view';

    public const PROGRAMS_CREATE = 'programs.create';

    public const PROGRAMS_EDIT = 'programs.edit';

    public const PROGRAMS_DELETE = 'programs.delete';

    public const PROGRAMS_ALL = 'programs.*';

    // Permisos de Convocatorias
    public const CALLS_VIEW = 'calls.view';

    public const CALLS_CREATE = 'calls.create';

    public const CALLS_EDIT = 'calls.edit';

    public const CALLS_DELETE = 'calls.delete';

    public const CALLS_PUBLISH = 'calls.publish';

    public const CALLS_ALL = 'calls.*';

    // Permisos de Noticias
    public const NEWS_VIEW = 'news.view';

    public const NEWS_CREATE = 'news.create';

    public const NEWS_EDIT = 'news.edit';

    public const NEWS_DELETE = 'news.delete';

    public const NEWS_PUBLISH = 'news.publish';

    public const NEWS_ALL = 'news.*';

    // Permisos de Documentos
    public const DOCUMENTS_VIEW = 'documents.view';

    public const DOCUMENTS_CREATE = 'documents.create';

    public const DOCUMENTS_EDIT = 'documents.edit';

    public const DOCUMENTS_DELETE = 'documents.delete';

    public const DOCUMENTS_ALL = 'documents.*';

    // Permisos de Eventos
    public const EVENTS_VIEW = 'events.view';

    public const EVENTS_CREATE = 'events.create';

    public const EVENTS_EDIT = 'events.edit';

    public const EVENTS_DELETE = 'events.delete';

    public const EVENTS_ALL = 'events.*';

    // Permisos de Usuarios
    public const USERS_VIEW = 'users.view';

    public const USERS_CREATE = 'users.create';

    public const USERS_EDIT = 'users.edit';

    public const USERS_DELETE = 'users.delete';

    public const USERS_ALL = 'users.*';

    // Permisos de Configuraci?n
    public const SETTINGS_VIEW = 'settings.view';

    public const SETTINGS_EDIT = 'settings.edit';

    public const SETTINGS_ALL = 'settings.*';

    // Permisos de Traducciones
    public const TRANSLATIONS_VIEW = 'translations.view';

    public const TRANSLATIONS_CREATE = 'translations.create';

    public const TRANSLATIONS_EDIT = 'translations.edit';

    public const TRANSLATIONS_DELETE = 'translations.delete';

    public const TRANSLATIONS_ALL = 'translations.*';

    // Permisos de Newsletter
    public const NEWSLETTER_VIEW = 'newsletter.view';

    public const NEWSLETTER_DELETE = 'newsletter.delete';

    public const NEWSLETTER_EXPORT = 'newsletter.export';

    public const NEWSLETTER_ALL = 'newsletter.*';

    /**
     * Obtener todos los permisos disponibles.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            // Programas
            self::PROGRAMS_VIEW,
            self::PROGRAMS_CREATE,
            self::PROGRAMS_EDIT,
            self::PROGRAMS_DELETE,
            self::PROGRAMS_ALL,

            // Convocatorias
            self::CALLS_VIEW,
            self::CALLS_CREATE,
            self::CALLS_EDIT,
            self::CALLS_DELETE,
            self::CALLS_PUBLISH,
            self::CALLS_ALL,

            // Noticias
            self::NEWS_VIEW,
            self::NEWS_CREATE,
            self::NEWS_EDIT,
            self::NEWS_DELETE,
            self::NEWS_PUBLISH,
            self::NEWS_ALL,

            // Documentos
            self::DOCUMENTS_VIEW,
            self::DOCUMENTS_CREATE,
            self::DOCUMENTS_EDIT,
            self::DOCUMENTS_DELETE,
            self::DOCUMENTS_ALL,

            // Eventos
            self::EVENTS_VIEW,
            self::EVENTS_CREATE,
            self::EVENTS_EDIT,
            self::EVENTS_DELETE,
            self::EVENTS_ALL,

            // Usuarios
            self::USERS_VIEW,
            self::USERS_CREATE,
            self::USERS_EDIT,
            self::USERS_DELETE,
            self::USERS_ALL,

            // Configuraci?n
            self::SETTINGS_VIEW,
            self::SETTINGS_EDIT,
            self::SETTINGS_ALL,

            // Traducciones
            self::TRANSLATIONS_VIEW,
            self::TRANSLATIONS_CREATE,
            self::TRANSLATIONS_EDIT,
            self::TRANSLATIONS_DELETE,
            self::TRANSLATIONS_ALL,

            // Newsletter
            self::NEWSLETTER_VIEW,
            self::NEWSLETTER_DELETE,
            self::NEWSLETTER_EXPORT,
            self::NEWSLETTER_ALL,
        ];
    }

    /**
     * Obtener permisos por m?dulo.
     *
     * @return array<string, array<string>>
     */
    public static function byModule(): array
    {
        return [
            'programs' => [
                self::PROGRAMS_VIEW,
                self::PROGRAMS_CREATE,
                self::PROGRAMS_EDIT,
                self::PROGRAMS_DELETE,
                self::PROGRAMS_ALL,
            ],
            'calls' => [
                self::CALLS_VIEW,
                self::CALLS_CREATE,
                self::CALLS_EDIT,
                self::CALLS_DELETE,
                self::CALLS_PUBLISH,
                self::CALLS_ALL,
            ],
            'news' => [
                self::NEWS_VIEW,
                self::NEWS_CREATE,
                self::NEWS_EDIT,
                self::NEWS_DELETE,
                self::NEWS_PUBLISH,
                self::NEWS_ALL,
            ],
            'documents' => [
                self::DOCUMENTS_VIEW,
                self::DOCUMENTS_CREATE,
                self::DOCUMENTS_EDIT,
                self::DOCUMENTS_DELETE,
                self::DOCUMENTS_ALL,
            ],
            'events' => [
                self::EVENTS_VIEW,
                self::EVENTS_CREATE,
                self::EVENTS_EDIT,
                self::EVENTS_DELETE,
                self::EVENTS_ALL,
            ],
            'users' => [
                self::USERS_VIEW,
                self::USERS_CREATE,
                self::USERS_EDIT,
                self::USERS_DELETE,
                self::USERS_ALL,
            ],
            'settings' => [
                self::SETTINGS_VIEW,
                self::SETTINGS_EDIT,
                self::SETTINGS_ALL,
            ],
            'translations' => [
                self::TRANSLATIONS_VIEW,
                self::TRANSLATIONS_CREATE,
                self::TRANSLATIONS_EDIT,
                self::TRANSLATIONS_DELETE,
                self::TRANSLATIONS_ALL,
            ],
            'newsletter' => [
                self::NEWSLETTER_VIEW,
                self::NEWSLETTER_DELETE,
                self::NEWSLETTER_EXPORT,
                self::NEWSLETTER_ALL,
            ],
        ];
    }

    /**
     * Obtener permisos de solo lectura (view).
     *
     * @return array<string>
     */
    public static function viewOnly(): array
    {
        return [
            self::PROGRAMS_VIEW,
            self::CALLS_VIEW,
            self::NEWS_VIEW,
            self::DOCUMENTS_VIEW,
            self::EVENTS_VIEW,
            self::SETTINGS_VIEW,
            self::TRANSLATIONS_VIEW,
            self::NEWSLETTER_VIEW,
        ];
    }
}
