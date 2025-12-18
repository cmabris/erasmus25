<?php

use App\Models\NewsPost;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_PUBLISH, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos incluyendo publish
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
        Permissions::NEWS_PUBLISH,
    ]);

    // Editor puede ver, crear y editar pero no publicar ni eliminar
    $editor->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::NEWS_VIEW,
    ]);
});

describe('NewsPostPolicy super-admin access', function () {
    it('allows super-admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeTrue()
            ->and($user->can('view', $newsPost))->toBeTrue()
            ->and($user->can('create', NewsPost::class))->toBeTrue()
            ->and($user->can('update', $newsPost))->toBeTrue()
            ->and($user->can('delete', $newsPost))->toBeTrue()
            ->and($user->can('publish', $newsPost))->toBeTrue()
            ->and($user->can('restore', $newsPost))->toBeTrue()
            ->and($user->can('forceDelete', $newsPost))->toBeTrue();
    });
});

describe('NewsPostPolicy admin access', function () {
    it('allows admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeTrue()
            ->and($user->can('view', $newsPost))->toBeTrue()
            ->and($user->can('create', NewsPost::class))->toBeTrue()
            ->and($user->can('update', $newsPost))->toBeTrue()
            ->and($user->can('delete', $newsPost))->toBeTrue()
            ->and($user->can('publish', $newsPost))->toBeTrue()
            ->and($user->can('restore', $newsPost))->toBeTrue()
            ->and($user->can('forceDelete', $newsPost))->toBeTrue();
    });
});

describe('NewsPostPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete or publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeTrue()
            ->and($user->can('view', $newsPost))->toBeTrue()
            ->and($user->can('create', NewsPost::class))->toBeTrue()
            ->and($user->can('update', $newsPost))->toBeTrue()
            ->and($user->can('delete', $newsPost))->toBeFalse()
            ->and($user->can('publish', $newsPost))->toBeFalse()
            ->and($user->can('restore', $newsPost))->toBeFalse()
            ->and($user->can('forceDelete', $newsPost))->toBeFalse();
    });
});

describe('NewsPostPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeTrue()
            ->and($user->can('view', $newsPost))->toBeTrue()
            ->and($user->can('create', NewsPost::class))->toBeFalse()
            ->and($user->can('update', $newsPost))->toBeFalse()
            ->and($user->can('delete', $newsPost))->toBeFalse()
            ->and($user->can('publish', $newsPost))->toBeFalse()
            ->and($user->can('restore', $newsPost))->toBeFalse()
            ->and($user->can('forceDelete', $newsPost))->toBeFalse();
    });
});

describe('NewsPostPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeFalse()
            ->and($user->can('view', $newsPost))->toBeFalse()
            ->and($user->can('create', NewsPost::class))->toBeFalse()
            ->and($user->can('update', $newsPost))->toBeFalse()
            ->and($user->can('delete', $newsPost))->toBeFalse()
            ->and($user->can('publish', $newsPost))->toBeFalse()
            ->and($user->can('restore', $newsPost))->toBeFalse()
            ->and($user->can('forceDelete', $newsPost))->toBeFalse();
    });
});

describe('NewsPostPolicy with direct permissions', function () {
    it('allows user with direct publish permission to publish', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_PUBLISH);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('publish', $newsPost))->toBeTrue()
            ->and($user->can('viewAny', NewsPost::class))->toBeFalse()
            ->and($user->can('delete', $newsPost))->toBeFalse();
    });

    it('allows user with direct view permission to view', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $newsPost = NewsPost::factory()->create();

        expect($user->can('viewAny', NewsPost::class))->toBeTrue()
            ->and($user->can('view', $newsPost))->toBeTrue()
            ->and($user->can('create', NewsPost::class))->toBeFalse();
    });
});
