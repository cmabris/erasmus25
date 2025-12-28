<?php

use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles (admin tiene todos)
    $admin->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
        Permissions::PROGRAMS_DELETE,
    ]);

    // Editor puede ver, crear y editar
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
    ]);
});

describe('ProgramPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeTrue()
            ->and($user->can('view', $program))->toBeTrue()
            ->and($user->can('create', Program::class))->toBeTrue()
            ->and($user->can('update', $program))->toBeTrue()
            ->and($user->can('delete', $program))->toBeTrue()
            ->and($user->can('restore', $program))->toBeTrue()
            ->and($user->can('forceDelete', $program))->toBeTrue();
    });
});

describe('ProgramPolicy admin access', function () {
    it('allows admin to perform all actions except forceDelete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeTrue()
            ->and($user->can('view', $program))->toBeTrue()
            ->and($user->can('create', Program::class))->toBeTrue()
            ->and($user->can('update', $program))->toBeTrue()
            ->and($user->can('delete', $program))->toBeTrue()
            ->and($user->can('restore', $program))->toBeTrue()
            ->and($user->can('forceDelete', $program))->toBeFalse(); // Solo super-admin puede hacer forceDelete
    });
});

describe('ProgramPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeTrue()
            ->and($user->can('view', $program))->toBeTrue()
            ->and($user->can('create', Program::class))->toBeTrue()
            ->and($user->can('update', $program))->toBeTrue()
            ->and($user->can('delete', $program))->toBeFalse()
            ->and($user->can('restore', $program))->toBeFalse()
            ->and($user->can('forceDelete', $program))->toBeFalse();
    });
});

describe('ProgramPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeTrue()
            ->and($user->can('view', $program))->toBeTrue()
            ->and($user->can('create', Program::class))->toBeFalse()
            ->and($user->can('update', $program))->toBeFalse()
            ->and($user->can('delete', $program))->toBeFalse()
            ->and($user->can('restore', $program))->toBeFalse()
            ->and($user->can('forceDelete', $program))->toBeFalse();
    });
});

describe('ProgramPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeFalse()
            ->and($user->can('view', $program))->toBeFalse()
            ->and($user->can('create', Program::class))->toBeFalse()
            ->and($user->can('update', $program))->toBeFalse()
            ->and($user->can('delete', $program))->toBeFalse()
            ->and($user->can('restore', $program))->toBeFalse()
            ->and($user->can('forceDelete', $program))->toBeFalse();
    });
});

describe('ProgramPolicy with direct permissions', function () {
    it('allows user with direct view permission to view', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_VIEW);
        $program = Program::factory()->create();

        expect($user->can('viewAny', Program::class))->toBeTrue()
            ->and($user->can('view', $program))->toBeTrue()
            ->and($user->can('create', Program::class))->toBeFalse();
    });

    it('allows user with direct create permission to create', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_CREATE);
        $program = Program::factory()->create();

        expect($user->can('create', Program::class))->toBeTrue()
            ->and($user->can('viewAny', Program::class))->toBeFalse();
    });

    it('allows user with direct edit permission to update', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_EDIT);
        $program = Program::factory()->create();

        expect($user->can('update', $program))->toBeTrue()
            ->and($user->can('viewAny', Program::class))->toBeFalse();
    });

    it('allows user with direct delete permission to delete and restore but not forceDelete', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::PROGRAMS_DELETE);
        $program = Program::factory()->create();

        expect($user->can('delete', $program))->toBeTrue()
            ->and($user->can('restore', $program))->toBeTrue()
            ->and($user->can('forceDelete', $program))->toBeFalse(); // Solo super-admin puede hacer forceDelete
    });
});
