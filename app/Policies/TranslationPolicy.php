<?php

namespace App\Policies;

use App\Models\Translation;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Traducciones.
 *
 * Las traducciones son elementos críticos del sistema de i18n, por lo que
 * solo administradores pueden gestionarlas.
 * El rol super-admin tiene acceso total a través del método before().
 */
class TranslationPolicy
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
        return $user->can(Permissions::TRANSLATIONS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Translation $translation): bool
    {
        return $user->can(Permissions::TRANSLATIONS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::TRANSLATIONS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Translation $translation): bool
    {
        return $user->can(Permissions::TRANSLATIONS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Translation $translation): bool
    {
        return $user->can(Permissions::TRANSLATIONS_DELETE);
    }
}
