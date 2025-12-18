<?php

use App\Models\NewsTag;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios (usa permisos de news)
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
    ]);

    // Editor puede ver, crear y editar
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

describe('NewsTagPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $newsTag = NewsTag::factory()->create();

        expect($user->can('viewAny', NewsTag::class))->toBeTrue()
            ->and($user->can('view', $newsTag))->toBeTrue()
            ->and($user->can('create', NewsTag::class))->toBeTrue()
            ->and($user->can('update', $newsTag))->toBeTrue()
            ->and($user->can('delete', $newsTag))->toBeTrue()
            ->and($user->can('restore', $newsTag))->toBeTrue()
            ->and($user->can('forceDelete', $newsTag))->toBeTrue();
    });
});

describe('NewsTagPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $newsTag = NewsTag::factory()->create();

        expect($user->can('viewAny', NewsTag::class))->toBeTrue()
            ->and($user->can('view', $newsTag))->toBeTrue()
            ->and($user->can('create', NewsTag::class))->toBeTrue()
            ->and($user->can('update', $newsTag))->toBeTrue()
            ->and($user->can('delete', $newsTag))->toBeTrue()
            ->and($user->can('restore', $newsTag))->toBeTrue()
            ->and($user->can('forceDelete', $newsTag))->toBeTrue();
    });
});

describe('NewsTagPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $newsTag = NewsTag::factory()->create();

        expect($user->can('viewAny', NewsTag::class))->toBeTrue()
            ->and($user->can('view', $newsTag))->toBeTrue()
            ->and($user->can('create', NewsTag::class))->toBeTrue()
            ->and($user->can('update', $newsTag))->toBeTrue()
            ->and($user->can('delete', $newsTag))->toBeFalse()
            ->and($user->can('restore', $newsTag))->toBeFalse()
            ->and($user->can('forceDelete', $newsTag))->toBeFalse();
    });
});

describe('NewsTagPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $newsTag = NewsTag::factory()->create();

        expect($user->can('viewAny', NewsTag::class))->toBeTrue()
            ->and($user->can('view', $newsTag))->toBeTrue()
            ->and($user->can('create', NewsTag::class))->toBeFalse()
            ->and($user->can('update', $newsTag))->toBeFalse()
            ->and($user->can('delete', $newsTag))->toBeFalse()
            ->and($user->can('restore', $newsTag))->toBeFalse()
            ->and($user->can('forceDelete', $newsTag))->toBeFalse();
    });
});

describe('NewsTagPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $newsTag = NewsTag::factory()->create();

        expect($user->can('viewAny', NewsTag::class))->toBeFalse()
            ->and($user->can('view', $newsTag))->toBeFalse()
            ->and($user->can('create', NewsTag::class))->toBeFalse()
            ->and($user->can('update', $newsTag))->toBeFalse()
            ->and($user->can('delete', $newsTag))->toBeFalse()
            ->and($user->can('restore', $newsTag))->toBeFalse()
            ->and($user->can('forceDelete', $newsTag))->toBeFalse();
    });
});
