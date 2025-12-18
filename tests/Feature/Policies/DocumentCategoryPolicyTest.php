<?php

use App\Models\DocumentCategory;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios (usa permisos de documents)
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
        Permissions::DOCUMENTS_DELETE,
    ]);

    // Editor puede ver, crear y editar
    $editor->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
    ]);
});

describe('DocumentCategoryPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $category = DocumentCategory::factory()->create();

        expect($user->can('viewAny', DocumentCategory::class))->toBeTrue()
            ->and($user->can('view', $category))->toBeTrue()
            ->and($user->can('create', DocumentCategory::class))->toBeTrue()
            ->and($user->can('update', $category))->toBeTrue()
            ->and($user->can('delete', $category))->toBeTrue()
            ->and($user->can('restore', $category))->toBeTrue()
            ->and($user->can('forceDelete', $category))->toBeTrue();
    });
});

describe('DocumentCategoryPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $category = DocumentCategory::factory()->create();

        expect($user->can('viewAny', DocumentCategory::class))->toBeTrue()
            ->and($user->can('view', $category))->toBeTrue()
            ->and($user->can('create', DocumentCategory::class))->toBeTrue()
            ->and($user->can('update', $category))->toBeTrue()
            ->and($user->can('delete', $category))->toBeTrue()
            ->and($user->can('restore', $category))->toBeTrue()
            ->and($user->can('forceDelete', $category))->toBeTrue();
    });
});

describe('DocumentCategoryPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $category = DocumentCategory::factory()->create();

        expect($user->can('viewAny', DocumentCategory::class))->toBeTrue()
            ->and($user->can('view', $category))->toBeTrue()
            ->and($user->can('create', DocumentCategory::class))->toBeTrue()
            ->and($user->can('update', $category))->toBeTrue()
            ->and($user->can('delete', $category))->toBeFalse()
            ->and($user->can('restore', $category))->toBeFalse()
            ->and($user->can('forceDelete', $category))->toBeFalse();
    });
});

describe('DocumentCategoryPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $category = DocumentCategory::factory()->create();

        expect($user->can('viewAny', DocumentCategory::class))->toBeTrue()
            ->and($user->can('view', $category))->toBeTrue()
            ->and($user->can('create', DocumentCategory::class))->toBeFalse()
            ->and($user->can('update', $category))->toBeFalse()
            ->and($user->can('delete', $category))->toBeFalse()
            ->and($user->can('restore', $category))->toBeFalse()
            ->and($user->can('forceDelete', $category))->toBeFalse();
    });
});

describe('DocumentCategoryPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $category = DocumentCategory::factory()->create();

        expect($user->can('viewAny', DocumentCategory::class))->toBeFalse()
            ->and($user->can('view', $category))->toBeFalse()
            ->and($user->can('create', DocumentCategory::class))->toBeFalse()
            ->and($user->can('update', $category))->toBeFalse()
            ->and($user->can('delete', $category))->toBeFalse()
            ->and($user->can('restore', $category))->toBeFalse()
            ->and($user->can('forceDelete', $category))->toBeFalse();
    });
});
