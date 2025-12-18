<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Convocatorias.
 *
 * Los permisos se verifican usando las constantes de App\Support\Permissions.
 * El rol super-admin tiene acceso total a través del método before().
 * Incluye método especial publish() para publicar convocatorias.
 */
class CallPolicy
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
        return $user->can(Permissions::CALLS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::CALLS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_DELETE);
    }

    /**
     * Determine whether the user can publish the model.
     *
     * Publicar una convocatoria implica cambiar su estado a publicado
     * y establecer published_at.
     */
    public function publish(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_PUBLISH);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_DELETE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Call $call): bool
    {
        return $user->can(Permissions::CALLS_DELETE);
    }
}
