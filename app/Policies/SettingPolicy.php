<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Configuraciones del Sistema.
 *
 * Las configuraciones son elementos críticos del sistema, por lo que
 * solo administradores y super-admins pueden gestionarlas.
 * El rol super-admin tiene acceso total a través del método before().
 */
class SettingPolicy
{
    /**
     * Perform pre-authorization checks.
     *
     * Si el usuario es super-admin, se le concede acceso total.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(Roles::SUPER_ADMIN)) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::SETTINGS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->can(Permissions::SETTINGS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     *
     * Solo super-admin puede crear configuraciones (se crean desde seeders normalmente).
     */
    public function create(User $user): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Setting $setting): bool
    {
        return $user->can(Permissions::SETTINGS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Solo super-admin puede eliminar configuraciones.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $user->hasRole(Roles::SUPER_ADMIN);
    }
}
