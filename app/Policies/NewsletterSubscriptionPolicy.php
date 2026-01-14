<?php

namespace App\Policies;

use App\Models\NewsletterSubscription;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;

/**
 * Policy para autorización de Suscripciones Newsletter.
 *
 * Los permisos se verifican usando las constantes de App\Support\Permissions.
 * El rol super-admin tiene acceso total a través del método before().
 * Las suscripciones se crean desde el frontend público, por lo que no hay
 * métodos create/update en el panel de administración.
 */
class NewsletterSubscriptionPolicy
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
        return $user->can(Permissions::NEWSLETTER_VIEW);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, NewsletterSubscription $newsletterSubscription): bool
    {
        return $user->can(Permissions::NEWSLETTER_VIEW);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, NewsletterSubscription $newsletterSubscription): bool
    {
        return $user->can(Permissions::NEWSLETTER_DELETE);
    }

    /**
     * Determine whether the user can export subscriptions.
     */
    public function export(User $user): bool
    {
        return $user->can(Permissions::NEWSLETTER_EXPORT);
    }
}
