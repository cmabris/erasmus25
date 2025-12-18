<?php

namespace App\Policies;

use App\Models\NewsTag;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Etiquetas de Noticias.
 *
 * Las etiquetas son sub-entidades de las noticias, por lo que
 * utilizan los mismos permisos del módulo news.
 * El rol super-admin tiene acceso total a través del método before().
 */
class NewsTagPolicy
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
        return $user->can(Permissions::NEWS_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NewsTag $newsTag): bool
    {
        return $user->can(Permissions::NEWS_VIEW);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::NEWS_CREATE);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, NewsTag $newsTag): bool
    {
        return $user->can(Permissions::NEWS_EDIT);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NewsTag $newsTag): bool
    {
        return $user->can(Permissions::NEWS_DELETE);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, NewsTag $newsTag): bool
    {
        return $user->can(Permissions::NEWS_DELETE);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, NewsTag $newsTag): bool
    {
        return $user->can(Permissions::NEWS_DELETE);
    }
}
