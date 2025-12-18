<?php

namespace App\Policies;

use App\Models\AcademicYear;
use App\Models\User;
use App\Support\Roles;

/**
 * Policy para autorización de Años Académicos.
 *
 * Los años académicos son datos de configuración del sistema.
 * Solo los administradores pueden gestionarlos, mientras que
 * todos los usuarios autenticados pueden verlos.
 */
class AcademicYearPolicy
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
     *
     * Todos los usuarios autenticados pueden ver años académicos.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * Todos los usuarios autenticados pueden ver años académicos.
     */
    public function view(User $user, AcademicYear $academicYear): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * Solo administradores pueden crear años académicos.
     */
    public function create(User $user): bool
    {
        return $user->hasRole(Roles::ADMIN);
    }

    /**
     * Determine whether the user can update the model.
     *
     * Solo administradores pueden editar años académicos.
     */
    public function update(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole(Roles::ADMIN);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * Solo administradores pueden eliminar años académicos.
     */
    public function delete(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole(Roles::ADMIN);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * Solo administradores pueden restaurar años académicos.
     */
    public function restore(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole(Roles::ADMIN);
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * Solo administradores pueden eliminar permanentemente años académicos.
     */
    public function forceDelete(User $user, AcademicYear $academicYear): bool
    {
        return $user->hasRole(Roles::ADMIN);
    }
}
