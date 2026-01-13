<?php

use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
});

describe('ActivityPolicy - Super Admin Access', function () {
    it('allows super-admin to view any activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        expect($user->can('viewAny', Activity::class))->toBeTrue();
    });

    it('allows super-admin to view an activity', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
        ]);

        expect($user->can('view', $activity))->toBeTrue();
    });
});

describe('ActivityPolicy - Admin Access', function () {
    it('allows admin to view any activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);

        expect($user->can('viewAny', Activity::class))->toBeTrue();
    });

    it('allows admin to view an activity', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
        ]);

        expect($user->can('view', $activity))->toBeTrue();
    });
});

describe('ActivityPolicy - Editor Access', function () {
    it('denies editor to view any activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);

        expect($user->can('viewAny', Activity::class))->toBeFalse();
    });

    it('denies editor to view an activity', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
        ]);

        expect($user->can('view', $activity))->toBeFalse();
    });
});

describe('ActivityPolicy - Viewer Access', function () {
    it('denies viewer to view any activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);

        expect($user->can('viewAny', Activity::class))->toBeFalse();
    });

    it('denies viewer to view an activity', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
        ]);

        expect($user->can('view', $activity))->toBeFalse();
    });
});

describe('ActivityPolicy - No Role Access', function () {
    it('denies users without role to view any activities', function () {
        $user = User::factory()->create();

        expect($user->can('viewAny', Activity::class))->toBeFalse();
    });

    it('denies users without role to view an activity', function () {
        $user = User::factory()->create();
        $activity = Activity::create([
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
        ]);

        expect($user->can('view', $activity))->toBeFalse();
    });
});
