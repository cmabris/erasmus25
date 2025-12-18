<?php

use App\Models\AcademicYear;
use App\Models\User;
use App\Support\Roles;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
});

describe('AcademicYearPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $academicYear = AcademicYear::factory()->create();

        expect($user->can('viewAny', AcademicYear::class))->toBeTrue()
            ->and($user->can('view', $academicYear))->toBeTrue()
            ->and($user->can('create', AcademicYear::class))->toBeTrue()
            ->and($user->can('update', $academicYear))->toBeTrue()
            ->and($user->can('delete', $academicYear))->toBeTrue()
            ->and($user->can('restore', $academicYear))->toBeTrue()
            ->and($user->can('forceDelete', $academicYear))->toBeTrue();
    });
});

describe('AcademicYearPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $academicYear = AcademicYear::factory()->create();

        expect($user->can('viewAny', AcademicYear::class))->toBeTrue()
            ->and($user->can('view', $academicYear))->toBeTrue()
            ->and($user->can('create', AcademicYear::class))->toBeTrue()
            ->and($user->can('update', $academicYear))->toBeTrue()
            ->and($user->can('delete', $academicYear))->toBeTrue()
            ->and($user->can('restore', $academicYear))->toBeTrue()
            ->and($user->can('forceDelete', $academicYear))->toBeTrue();
    });
});

describe('AcademicYearPolicy editor access', function () {
    it('allows editor to only view academic years', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $academicYear = AcademicYear::factory()->create();

        expect($user->can('viewAny', AcademicYear::class))->toBeTrue()
            ->and($user->can('view', $academicYear))->toBeTrue()
            ->and($user->can('create', AcademicYear::class))->toBeFalse()
            ->and($user->can('update', $academicYear))->toBeFalse()
            ->and($user->can('delete', $academicYear))->toBeFalse()
            ->and($user->can('restore', $academicYear))->toBeFalse()
            ->and($user->can('forceDelete', $academicYear))->toBeFalse();
    });
});

describe('AcademicYearPolicy viewer access', function () {
    it('allows viewer to only view academic years', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $academicYear = AcademicYear::factory()->create();

        expect($user->can('viewAny', AcademicYear::class))->toBeTrue()
            ->and($user->can('view', $academicYear))->toBeTrue()
            ->and($user->can('create', AcademicYear::class))->toBeFalse()
            ->and($user->can('update', $academicYear))->toBeFalse()
            ->and($user->can('delete', $academicYear))->toBeFalse()
            ->and($user->can('restore', $academicYear))->toBeFalse()
            ->and($user->can('forceDelete', $academicYear))->toBeFalse();
    });
});

describe('AcademicYearPolicy no role access', function () {
    it('allows viewing but denies modification for user without roles', function () {
        $user = User::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        // Users without roles can still view academic years (public data)
        expect($user->can('viewAny', AcademicYear::class))->toBeTrue()
            ->and($user->can('view', $academicYear))->toBeTrue()
            ->and($user->can('create', AcademicYear::class))->toBeFalse()
            ->and($user->can('update', $academicYear))->toBeFalse()
            ->and($user->can('delete', $academicYear))->toBeFalse()
            ->and($user->can('restore', $academicYear))->toBeFalse()
            ->and($user->can('forceDelete', $academicYear))->toBeFalse();
    });
});
