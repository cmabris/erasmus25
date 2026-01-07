<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;
use Spatie\Permission\Models\Role;

/**
 * Policy para autorización de Roles.
 *
 * Solo el rol super-admin puede gestionar roles y permisos.
 * Los roles del sistema (super-admin, admin, editor, viewer) no pueden eliminarse.
 */
class RolePolicy
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
        // Solo super-admin puede ver roles
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Role $role): bool
    {
        // Solo super-admin puede ver roles
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo super-admin puede crear roles
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Role $role): bool
    {
        // Solo super-admin puede actualizar roles
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Role $role): bool
    {
        // Los roles del sistema no pueden eliminarse
        if (in_array($role->name, Roles::all(), true)) {
            return false;
        }

        // Un rol con usuarios asignados no puede eliminarse
        if ($role->users()->count() > 0) {
            return false;
        }

        // Solo super-admin puede eliminar roles
        return false;
    }
}
