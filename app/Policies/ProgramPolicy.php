<?php

namespace App\Policies;

use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Programas.
 *
 * Los permisos se verifican usando las constantes de App\Support\Permissions.
 * El rol super-admin tiene acceso total a través del método before().
 */
class ProgramPolicy
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
        return $user->can(Permissions::PROGRAMS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::PROGRAMS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Program $program): bool
    {
        return $user->can(Permissions::PROGRAMS_DELETE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * Solo super-admin puede hacer forceDelete, y solo si el programa
     * no tiene relaciones con otros modelos (calls, newsPosts).
     */
    public function forceDelete(User $user, Program $program): bool
    {
        // Solo super-admin puede hacer forceDelete
        if (! $user->hasRole(Roles::SUPER_ADMIN)) {
            return false;
        }

        // Verificar que no tenga relaciones antes de permitir forceDelete
        $hasRelations = $program->calls()->exists() || $program->newsPosts()->exists();

        return ! $hasRelations;
    }
}
