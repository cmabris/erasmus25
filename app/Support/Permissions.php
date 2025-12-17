<?php

namespace App\Support;

/**
 * Constantes para los permisos del sistema.
 *
 * Los permisos están organizados por módulo:
 * - programs: Gestión de programas Erasmus+
 * - calls: Gestión de convocatorias
 * - news: Gestión de noticias
 * - documents: Gestión de documentos
 * - events: Gestión de eventos
 * - users: Gestión de usuarios
 *
 * Cada módulo tiene permisos específicos:
 * - view: Ver listados y detalles
 * - create: Crear nuevos registros
 * - edit: Editar registros existentes
 * - delete: Eliminar registros
 * - publish: Publicar contenido (solo para calls y news)
 * - *: Todos los permisos del módulo
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
        ];
    }

    /**
     * Obtener permisos por módulo.
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
        ];
    }
}
