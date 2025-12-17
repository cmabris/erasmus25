<?php

namespace App\Support;

/**
 * Constantes para los roles del sistema.
 *
 * Los roles están organizados por nivel de acceso:
 * - super-admin: Acceso total al sistema
 * - admin: Gestión completa de contenido y convocatorias
 * - editor: Creación y edición de contenido
 * - viewer: Solo lectura
 */
class Roles
{
    /**
     * Super Administrador - Acceso total al sistema
     */
    public const SUPER_ADMIN = 'super-admin';

    /**
     * Administrador - Gestión completa de contenido y convocatorias
     */
    public const ADMIN = 'admin';

    /**
     * Editor - Creación y edición de contenido
     */
    public const EDITOR = 'editor';

    /**
     * Viewer - Solo lectura
     */
    public const VIEWER = 'viewer';

    /**
     * Obtener todos los roles disponibles.
     *
     * @return array<string>
     */
    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::EDITOR,
            self::VIEWER,
        ];
    }

    /**
     * Obtener los roles administrativos (super-admin y admin).
     *
     * @return array<string>
     */
    public static function administrative(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
        ];
    }

    /**
     * Verificar si un rol es administrativo.
     */
    public static function isAdministrative(string $role): bool
    {
        return in_array($role, self::administrative(), true);
    }
}
