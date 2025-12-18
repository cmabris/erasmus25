<?php

use App\Models\ErasmusEvent;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::EVENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
        Permissions::EVENTS_DELETE,
    ]);

    // Editor puede ver, crear y editar
    $editor->givePermissionTo([
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::EVENTS_VIEW,
    ]);
});

describe('ErasmusEventPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('view', $event))->toBeTrue()
            ->and($user->can('create', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('update', $event))->toBeTrue()
            ->and($user->can('delete', $event))->toBeTrue()
            ->and($user->can('restore', $event))->toBeTrue()
            ->and($user->can('forceDelete', $event))->toBeTrue();
    });
});

describe('ErasmusEventPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('view', $event))->toBeTrue()
            ->and($user->can('create', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('update', $event))->toBeTrue()
            ->and($user->can('delete', $event))->toBeTrue()
            ->and($user->can('restore', $event))->toBeTrue()
            ->and($user->can('forceDelete', $event))->toBeTrue();
    });
});

describe('ErasmusEventPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('view', $event))->toBeTrue()
            ->and($user->can('create', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('update', $event))->toBeTrue()
            ->and($user->can('delete', $event))->toBeFalse()
            ->and($user->can('restore', $event))->toBeFalse()
            ->and($user->can('forceDelete', $event))->toBeFalse();
    });
});

describe('ErasmusEventPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('view', $event))->toBeTrue()
            ->and($user->can('create', ErasmusEvent::class))->toBeFalse()
            ->and($user->can('update', $event))->toBeFalse()
            ->and($user->can('delete', $event))->toBeFalse()
            ->and($user->can('restore', $event))->toBeFalse()
            ->and($user->can('forceDelete', $event))->toBeFalse();
    });
});

describe('ErasmusEventPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeFalse()
            ->and($user->can('view', $event))->toBeFalse()
            ->and($user->can('create', ErasmusEvent::class))->toBeFalse()
            ->and($user->can('update', $event))->toBeFalse()
            ->and($user->can('delete', $event))->toBeFalse()
            ->and($user->can('restore', $event))->toBeFalse()
            ->and($user->can('forceDelete', $event))->toBeFalse();
    });
});

describe('ErasmusEventPolicy with direct permissions', function () {
    it('allows user with direct view permission to view', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_VIEW);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('viewAny', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('view', $event))->toBeTrue()
            ->and($user->can('create', ErasmusEvent::class))->toBeFalse();
    });

    it('allows user with direct create permission to create', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::EVENTS_CREATE);
        $event = ErasmusEvent::factory()->create();

        expect($user->can('create', ErasmusEvent::class))->toBeTrue()
            ->and($user->can('viewAny', ErasmusEvent::class))->toBeFalse();
    });
});
