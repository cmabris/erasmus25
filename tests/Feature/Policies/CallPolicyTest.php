<?php

use App\Models\Call;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_DELETE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_PUBLISH, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos incluyendo publish
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
        Permissions::CALLS_PUBLISH,
    ]);

    // Editor puede ver, crear y editar pero no publicar ni eliminar
    $editor->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::CALLS_VIEW,
    ]);
});

describe('CallPolicy super-admin access', function () {
    it('allows super-admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeTrue()
            ->and($user->can('view', $call))->toBeTrue()
            ->and($user->can('create', Call::class))->toBeTrue()
            ->and($user->can('update', $call))->toBeTrue()
            ->and($user->can('delete', $call))->toBeTrue()
            ->and($user->can('publish', $call))->toBeTrue()
            ->and($user->can('restore', $call))->toBeTrue()
            ->and($user->can('forceDelete', $call))->toBeTrue();
    });
});

describe('CallPolicy admin access', function () {
    it('allows admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeTrue()
            ->and($user->can('view', $call))->toBeTrue()
            ->and($user->can('create', Call::class))->toBeTrue()
            ->and($user->can('update', $call))->toBeTrue()
            ->and($user->can('delete', $call))->toBeTrue()
            ->and($user->can('publish', $call))->toBeTrue()
            ->and($user->can('restore', $call))->toBeTrue()
            ->and($user->can('forceDelete', $call))->toBeTrue();
    });
});

describe('CallPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete or publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeTrue()
            ->and($user->can('view', $call))->toBeTrue()
            ->and($user->can('create', Call::class))->toBeTrue()
            ->and($user->can('update', $call))->toBeTrue()
            ->and($user->can('delete', $call))->toBeFalse()
            ->and($user->can('publish', $call))->toBeFalse()
            ->and($user->can('restore', $call))->toBeFalse()
            ->and($user->can('forceDelete', $call))->toBeFalse();
    });
});

describe('CallPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeTrue()
            ->and($user->can('view', $call))->toBeTrue()
            ->and($user->can('create', Call::class))->toBeFalse()
            ->and($user->can('update', $call))->toBeFalse()
            ->and($user->can('delete', $call))->toBeFalse()
            ->and($user->can('publish', $call))->toBeFalse()
            ->and($user->can('restore', $call))->toBeFalse()
            ->and($user->can('forceDelete', $call))->toBeFalse();
    });
});

describe('CallPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeFalse()
            ->and($user->can('view', $call))->toBeFalse()
            ->and($user->can('create', Call::class))->toBeFalse()
            ->and($user->can('update', $call))->toBeFalse()
            ->and($user->can('delete', $call))->toBeFalse()
            ->and($user->can('publish', $call))->toBeFalse()
            ->and($user->can('restore', $call))->toBeFalse()
            ->and($user->can('forceDelete', $call))->toBeFalse();
    });
});

describe('CallPolicy with direct permissions', function () {
    it('allows user with direct publish permission to publish', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_PUBLISH);
        $call = Call::factory()->create();

        expect($user->can('publish', $call))->toBeTrue()
            ->and($user->can('viewAny', Call::class))->toBeFalse()
            ->and($user->can('delete', $call))->toBeFalse();
    });

    it('allows user with direct view permission to view', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_VIEW);
        $call = Call::factory()->create();

        expect($user->can('viewAny', Call::class))->toBeTrue()
            ->and($user->can('view', $call))->toBeTrue()
            ->and($user->can('create', Call::class))->toBeFalse();
    });
});
