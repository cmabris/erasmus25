<?php

use App\Models\Document;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear los permisos necesarios
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

describe('DocumentPolicy super-admin access', function () {
    it('allows super-admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeTrue()
            ->and($user->can('view', $document))->toBeTrue()
            ->and($user->can('create', Document::class))->toBeTrue()
            ->and($user->can('update', $document))->toBeTrue()
            ->and($user->can('delete', $document))->toBeTrue()
            ->and($user->can('restore', $document))->toBeTrue()
            ->and($user->can('forceDelete', $document))->toBeTrue();
    });
});

describe('DocumentPolicy admin access', function () {
    it('allows admin to perform all actions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeTrue()
            ->and($user->can('view', $document))->toBeTrue()
            ->and($user->can('create', Document::class))->toBeTrue()
            ->and($user->can('update', $document))->toBeTrue()
            ->and($user->can('delete', $document))->toBeTrue()
            ->and($user->can('restore', $document))->toBeTrue()
            ->and($user->can('forceDelete', $document))->toBeTrue();
    });
});

describe('DocumentPolicy editor access', function () {
    it('allows editor to view, create and edit but not delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeTrue()
            ->and($user->can('view', $document))->toBeTrue()
            ->and($user->can('create', Document::class))->toBeTrue()
            ->and($user->can('update', $document))->toBeTrue()
            ->and($user->can('delete', $document))->toBeFalse()
            ->and($user->can('restore', $document))->toBeFalse()
            ->and($user->can('forceDelete', $document))->toBeFalse();
    });
});

describe('DocumentPolicy viewer access', function () {
    it('allows viewer to only view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeTrue()
            ->and($user->can('view', $document))->toBeTrue()
            ->and($user->can('create', Document::class))->toBeFalse()
            ->and($user->can('update', $document))->toBeFalse()
            ->and($user->can('delete', $document))->toBeFalse()
            ->and($user->can('restore', $document))->toBeFalse()
            ->and($user->can('forceDelete', $document))->toBeFalse();
    });
});

describe('DocumentPolicy no role access', function () {
    it('denies all actions for user without roles', function () {
        $user = User::factory()->create();
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeFalse()
            ->and($user->can('view', $document))->toBeFalse()
            ->and($user->can('create', Document::class))->toBeFalse()
            ->and($user->can('update', $document))->toBeFalse()
            ->and($user->can('delete', $document))->toBeFalse()
            ->and($user->can('restore', $document))->toBeFalse()
            ->and($user->can('forceDelete', $document))->toBeFalse();
    });
});

describe('DocumentPolicy with direct permissions', function () {
    it('allows user with direct view permission to view', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $document = Document::factory()->create();

        expect($user->can('viewAny', Document::class))->toBeTrue()
            ->and($user->can('view', $document))->toBeTrue()
            ->and($user->can('create', Document::class))->toBeFalse();
    });

    it('allows user with direct delete permission to delete', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_DELETE);
        $document = Document::factory()->create();

        expect($user->can('delete', $document))->toBeTrue()
            ->and($user->can('restore', $document))->toBeTrue()
            ->and($user->can('forceDelete', $document))->toBeTrue();
    });
});
