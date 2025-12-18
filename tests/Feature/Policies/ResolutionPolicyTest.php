<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\Resolution;
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

    // Crear un programa y año académico base para evitar conflictos de unicidad
    $this->program = Program::factory()->create([
        'code' => 'TEST-'.uniqid(),
        'name' => 'Test Program '.uniqid(),
        'slug' => 'test-program-'.uniqid(),
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2099-2100',
    ]);

    $this->call = Call::factory()->create([
        'program_id' => $this->program->id,
        'academic_year_id' => $this->academicYear->id,
    ]);
});

describe('ResolutionPolicy super-admin access', function () {
    it('allows super-admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('viewAny', Resolution::class))->toBeTrue()
            ->and($user->can('view', $resolution))->toBeTrue()
            ->and($user->can('create', Resolution::class))->toBeTrue()
            ->and($user->can('update', $resolution))->toBeTrue()
            ->and($user->can('delete', $resolution))->toBeTrue()
            ->and($user->can('publish', $resolution))->toBeTrue()
            ->and($user->can('restore', $resolution))->toBeTrue()
            ->and($user->can('forceDelete', $resolution))->toBeTrue();
    });
});

describe('ResolutionPolicy admin access', function () {
    it('allows admin to perform all actions including publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('viewAny', Resolution::class))->toBeTrue()
            ->and($user->can('view', $resolution))->toBeTrue()
            ->and($user->can('create', Resolution::class))->toBeTrue()
            ->and($user->can('update', $resolution))->toBeTrue()
            ->and($user->can('delete', $resolution))->toBeTrue()
            ->and($user->can('publish', $resolution))->toBeTrue()
            ->and($user->can('restore', $resolution))->toBeTrue()
            ->and($user->can('forceDelete', $resolution))->toBeTrue();
    });
});

describe('ResolutionPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete or publish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('viewAny', Resolution::class))->toBeTrue()
            ->and($user->can('view', $resolution))->toBeTrue()
            ->and($user->can('create', Resolution::class))->toBeTrue()
            ->and($user->can('update', $resolution))->toBeTrue()
            ->and($user->can('delete', $resolution))->toBeFalse()
            ->and($user->can('publish', $resolution))->toBeFalse()
            ->and($user->can('restore', $resolution))->toBeFalse()
            ->and($user->can('forceDelete', $resolution))->toBeFalse();
    });
});

describe('ResolutionPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('viewAny', Resolution::class))->toBeTrue()
            ->and($user->can('view', $resolution))->toBeTrue()
            ->and($user->can('create', Resolution::class))->toBeFalse()
            ->and($user->can('update', $resolution))->toBeFalse()
            ->and($user->can('delete', $resolution))->toBeFalse()
            ->and($user->can('publish', $resolution))->toBeFalse()
            ->and($user->can('restore', $resolution))->toBeFalse()
            ->and($user->can('forceDelete', $resolution))->toBeFalse();
    });
});

describe('ResolutionPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('viewAny', Resolution::class))->toBeFalse()
            ->and($user->can('view', $resolution))->toBeFalse()
            ->and($user->can('create', Resolution::class))->toBeFalse()
            ->and($user->can('update', $resolution))->toBeFalse()
            ->and($user->can('delete', $resolution))->toBeFalse()
            ->and($user->can('publish', $resolution))->toBeFalse()
            ->and($user->can('restore', $resolution))->toBeFalse()
            ->and($user->can('forceDelete', $resolution))->toBeFalse();
    });
});

describe('ResolutionPolicy with direct permissions', function () {
    it('allows user with direct publish permission to publish resolutions', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::CALLS_PUBLISH);
        $resolution = Resolution::factory()->create([
            'call_id' => $this->call->id,
        ]);

        expect($user->can('publish', $resolution))->toBeTrue()
            ->and($user->can('viewAny', Resolution::class))->toBeFalse();
    });
});
