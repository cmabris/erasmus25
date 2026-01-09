<?php

use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_EDIT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $editor->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::SETTINGS_VIEW,
    ]);
});

describe('SettingPolicy - Super Admin Access', function () {
    it('allows super-admin to view any settings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        expect($user->can('viewAny', Setting::class))->toBeTrue();
    });

    it('allows super-admin to view a setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $setting = Setting::factory()->create();

        expect($user->can('view', $setting))->toBeTrue();
    });

    it('allows super-admin to create settings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);

        expect($user->can('create', Setting::class))->toBeTrue();
    });

    it('allows super-admin to update settings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $setting = Setting::factory()->create();

        expect($user->can('update', $setting))->toBeTrue();
    });

    it('allows super-admin to delete settings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $setting = Setting::factory()->create();

        expect($user->can('delete', $setting))->toBeTrue();
    });
});

describe('SettingPolicy - View Any', function () {
    it('allows users with SETTINGS_VIEW permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);

        expect($user->can('viewAny', Setting::class))->toBeTrue();
    });

    it('denies users without SETTINGS_VIEW permission', function () {
        $user = User::factory()->create();

        expect($user->can('viewAny', Setting::class))->toBeFalse();
    });
});

describe('SettingPolicy - View', function () {
    it('allows users with SETTINGS_VIEW permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $setting = Setting::factory()->create();

        expect($user->can('view', $setting))->toBeTrue();
    });

    it('denies users without SETTINGS_VIEW permission', function () {
        $user = User::factory()->create();
        $setting = Setting::factory()->create();

        expect($user->can('view', $setting))->toBeFalse();
    });
});

describe('SettingPolicy - Create', function () {
    it('allows only super-admin to create settings', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);

        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);

        expect($superAdmin->can('create', Setting::class))->toBeTrue();
        expect($admin->can('create', Setting::class))->toBeFalse();
        expect($editor->can('create', Setting::class))->toBeFalse();
    });
});

describe('SettingPolicy - Update', function () {
    it('allows users with SETTINGS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $setting = Setting::factory()->create();

        expect($user->can('update', $setting))->toBeTrue();
    });

    it('allows users with SETTINGS_EDIT permission (editor)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $setting = Setting::factory()->create();

        expect($user->can('update', $setting))->toBeTrue();
    });

    it('denies users without SETTINGS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $setting = Setting::factory()->create();

        expect($user->can('update', $setting))->toBeFalse();
    });

    it('denies users without any permission', function () {
        $user = User::factory()->create();
        $setting = Setting::factory()->create();

        expect($user->can('update', $setting))->toBeFalse();
    });
});

describe('SettingPolicy - Delete', function () {
    it('allows only super-admin to delete settings', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);
        $setting = Setting::factory()->create();

        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);

        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);

        expect($superAdmin->can('delete', $setting))->toBeTrue();
        expect($admin->can('delete', $setting))->toBeFalse();
        expect($editor->can('delete', $setting))->toBeFalse();
    });
});
