<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Roles;
use Spatie\Activitylog\Models\Activity;

/**
 * Policy para autorización de Logs de Auditoría (Activity).
 *
 * Los logs de auditoría son información sensible del sistema, por lo que
 * solo administradores y super-admins pueden verlos.
 * El rol super-admin tiene acceso total a través del método before().
 * Los logs son de solo lectura - no se pueden crear, editar ni eliminar.
 */
class ActivityPolicy
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
     * Determine whether the user can view any activities.
     *
     * Solo admin y super-admin pueden ver el listado de logs.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(Roles::ADMIN) || $user->hasRole(Roles::SUPER_ADMIN);
    }

    /**
     * Determine whether the user can view the activity.
     *
     * Solo admin y super-admin pueden ver el detalle de un log.
     */
    public function view(User $user, Activity $activity): bool
    {
        return $user->hasRole(Roles::ADMIN) || $user->hasRole(Roles::SUPER_ADMIN);
    }
}
