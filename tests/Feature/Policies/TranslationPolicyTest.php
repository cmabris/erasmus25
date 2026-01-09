<?php

use App\Models\Language;
use App\Models\Program;
use App\Models\Translation;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
        Permissions::TRANSLATIONS_CREATE,
        Permissions::TRANSLATIONS_EDIT,
        Permissions::TRANSLATIONS_DELETE,
    ]);

    // Editor puede ver, crear y editar
    $editor->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
        Permissions::TRANSLATIONS_CREATE,
        Permissions::TRANSLATIONS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
    ]);
});

describe('TranslationPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        expect($user->can('viewAny', Translation::class))->toBeTrue()
            ->and($user->can('view', $translation))->toBeTrue()
            ->and($user->can('create', Translation::class))->toBeTrue()
            ->and($user->can('update', $translation))->toBeTrue()
            ->and($user->can('delete', $translation))->toBeTrue();
    });
});

describe('TranslationPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        expect($user->can('viewAny', Translation::class))->toBeTrue()
            ->and($user->can('view', $translation))->toBeTrue()
            ->and($user->can('create', Translation::class))->toBeTrue()
            ->and($user->can('update', $translation))->toBeTrue()
            ->and($user->can('delete', $translation))->toBeTrue();
    });
});

describe('TranslationPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        expect($user->can('viewAny', Translation::class))->toBeTrue()
            ->and($user->can('view', $translation))->toBeTrue()
            ->and($user->can('create', Translation::class))->toBeTrue()
            ->and($user->can('update', $translation))->toBeTrue()
            ->and($user->can('delete', $translation))->toBeFalse();
    });
});

describe('TranslationPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        expect($user->can('viewAny', Translation::class))->toBeTrue()
            ->and($user->can('view', $translation))->toBeTrue()
            ->and($user->can('create', Translation::class))->toBeFalse()
            ->and($user->can('update', $translation))->toBeFalse()
            ->and($user->can('delete', $translation))->toBeFalse();
    });
});

describe('TranslationPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        expect($user->can('viewAny', Translation::class))->toBeFalse()
            ->and($user->can('view', $translation))->toBeFalse()
            ->and($user->can('create', Translation::class))->toBeFalse()
            ->and($user->can('update', $translation))->toBeFalse()
            ->and($user->can('delete', $translation))->toBeFalse();
    });
});
