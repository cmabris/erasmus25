<?php

use App\Models\NewsletterSubscription;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWSLETTER_EXPORT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
        Permissions::NEWSLETTER_DELETE,
        Permissions::NEWSLETTER_EXPORT,
    ]);

    // Editor puede ver y exportar, pero no eliminar
    $editor->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
        Permissions::NEWSLETTER_EXPORT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::NEWSLETTER_VIEW,
    ]);
});

describe('NewsletterSubscriptionPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $subscription = NewsletterSubscription::factory()->create();

        expect($user->can('viewAny', NewsletterSubscription::class))->toBeTrue()
            ->and($user->can('view', $subscription))->toBeTrue()
            ->and($user->can('delete', $subscription))->toBeTrue()
            ->and($user->can('export', NewsletterSubscription::class))->toBeTrue();
    });
});

describe('NewsletterSubscriptionPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $subscription = NewsletterSubscription::factory()->create();

        expect($user->can('viewAny', NewsletterSubscription::class))->toBeTrue()
            ->and($user->can('view', $subscription))->toBeTrue()
            ->and($user->can('delete', $subscription))->toBeTrue()
            ->and($user->can('export', NewsletterSubscription::class))->toBeTrue();
    });
});

describe('NewsletterSubscriptionPolicy editor access', function () {
    it('allows editor to view and export but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $subscription = NewsletterSubscription::factory()->create();

        expect($user->can('viewAny', NewsletterSubscription::class))->toBeTrue()
            ->and($user->can('view', $subscription))->toBeTrue()
            ->and($user->can('delete', $subscription))->toBeFalse()
            ->and($user->can('export', NewsletterSubscription::class))->toBeTrue();
    });
});

describe('NewsletterSubscriptionPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $subscription = NewsletterSubscription::factory()->create();

        expect($user->can('viewAny', NewsletterSubscription::class))->toBeTrue()
            ->and($user->can('view', $subscription))->toBeTrue()
            ->and($user->can('delete', $subscription))->toBeFalse()
            ->and($user->can('export', NewsletterSubscription::class))->toBeFalse();
    });
});

describe('NewsletterSubscriptionPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $subscription = NewsletterSubscription::factory()->create();

        expect($user->can('viewAny', NewsletterSubscription::class))->toBeFalse()
            ->and($user->can('view', $subscription))->toBeFalse()
            ->and($user->can('delete', $subscription))->toBeFalse()
            ->and($user->can('export', NewsletterSubscription::class))->toBeFalse();
    });
});
