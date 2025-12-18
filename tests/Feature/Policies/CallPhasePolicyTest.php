<?php

use App\Models\CallPhase;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios (usa permisos de calls)
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
    ]);

    // Editor puede ver, crear y editar
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

describe('CallPhasePolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $callPhase = CallPhase::factory()->create();

        expect($user->can('viewAny', CallPhase::class))->toBeTrue()
            ->and($user->can('view', $callPhase))->toBeTrue()
            ->and($user->can('create', CallPhase::class))->toBeTrue()
            ->and($user->can('update', $callPhase))->toBeTrue()
            ->and($user->can('delete', $callPhase))->toBeTrue()
            ->and($user->can('restore', $callPhase))->toBeTrue()
            ->and($user->can('forceDelete', $callPhase))->toBeTrue();
    });
});

describe('CallPhasePolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $callPhase = CallPhase::factory()->create();

        expect($user->can('viewAny', CallPhase::class))->toBeTrue()
            ->and($user->can('view', $callPhase))->toBeTrue()
            ->and($user->can('create', CallPhase::class))->toBeTrue()
            ->and($user->can('update', $callPhase))->toBeTrue()
            ->and($user->can('delete', $callPhase))->toBeTrue()
            ->and($user->can('restore', $callPhase))->toBeTrue()
            ->and($user->can('forceDelete', $callPhase))->toBeTrue();
    });
});

describe('CallPhasePolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $callPhase = CallPhase::factory()->create();

        expect($user->can('viewAny', CallPhase::class))->toBeTrue()
            ->and($user->can('view', $callPhase))->toBeTrue()
            ->and($user->can('create', CallPhase::class))->toBeTrue()
            ->and($user->can('update', $callPhase))->toBeTrue()
            ->and($user->can('delete', $callPhase))->toBeFalse()
            ->and($user->can('restore', $callPhase))->toBeFalse()
            ->and($user->can('forceDelete', $callPhase))->toBeFalse();
    });
});

describe('CallPhasePolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $callPhase = CallPhase::factory()->create();

        expect($user->can('viewAny', CallPhase::class))->toBeTrue()
            ->and($user->can('view', $callPhase))->toBeTrue()
            ->and($user->can('create', CallPhase::class))->toBeFalse()
            ->and($user->can('update', $callPhase))->toBeFalse()
            ->and($user->can('delete', $callPhase))->toBeFalse()
            ->and($user->can('restore', $callPhase))->toBeFalse()
            ->and($user->can('forceDelete', $callPhase))->toBeFalse();
    });
});

describe('CallPhasePolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $callPhase = CallPhase::factory()->create();

        expect($user->can('viewAny', CallPhase::class))->toBeFalse()
            ->and($user->can('view', $callPhase))->toBeFalse()
            ->and($user->can('create', CallPhase::class))->toBeFalse()
            ->and($user->can('update', $callPhase))->toBeFalse()
            ->and($user->can('delete', $callPhase))->toBeFalse()
            ->and($user->can('restore', $callPhase))->toBeFalse()
            ->and($user->can('forceDelete', $callPhase))->toBeFalse();
    });
});
