<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Usuarios.
 *
 * Los permisos se verifican usando las constantes de App\Support\Permissions.
 * El rol super-admin tiene acceso total a través del método before().
 * Solo el super-admin tiene permisos de users.*, por lo que
 * efectivamente solo este rol puede gestionar usuarios.
 */
class UserPolicy
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
        return $user->can(Permissions::USERS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Un usuario siempre puede ver su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can(Permissions::USERS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::USERS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Un usuario siempre puede actualizar su propio perfil
        if ($user->id === $model->id) {
            return true;
        }

        return $user->can(Permissions::USERS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Un usuario no puede eliminarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can(Permissions::USERS_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can(Permissions::USERS_DELETE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Un usuario no puede eliminarse a sí mismo
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can(Permissions::USERS_DELETE);
    }

    /**
     * Determine whether the user can assign roles to the model.
     */
    public function assignRoles(User $user, User $model): bool
    {
        // Solo usuarios con permisos de edición de usuarios pueden asignar roles
        // y no pueden modificar sus propios roles
        if ($user->id === $model->id) {
            return false;
        }

        return $user->can(Permissions::USERS_EDIT);
    }
}
