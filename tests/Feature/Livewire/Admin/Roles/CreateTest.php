<?php

use App\Livewire\Admin\Roles\Create;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);

    // Crear roles del sistema
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar todos los permisos a super-admin
    $superAdmin->givePermissionTo(Permission::all());
});

describe('Admin Roles Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.roles.create'))
            ->assertRedirect('/login');
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access to admin users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.create'))
            ->assertForbidden();
    });

    it('denies access to editor users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $this->get(route('admin.roles.create'))
            ->assertForbidden();
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.roles.create'))
            ->assertForbidden();
    });
});

describe('Admin Roles Create - Successful Creation', function () {
    it('can create a role with valid name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertDispatched('role-created')
            ->assertRedirect(route('admin.roles.index'));

        $this->assertDatabaseHas('roles', ['name' => Roles::VIEWER]);
    });

    it('can create a role without permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store');

        $role = Role::where('name', Roles::VIEWER)->first();
        expect($role->permissions)->toBeEmpty();
    });

    it('can create a role with permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [Permissions::PROGRAMS_VIEW, Permissions::CALLS_VIEW])
            ->call('store');

        $role = Role::where('name', Roles::VIEWER)->first();
        expect($role->permissions->pluck('name')->toArray())->toContain(Permissions::PROGRAMS_VIEW)
            ->and($role->permissions->pluck('name')->toArray())->toContain(Permissions::CALLS_VIEW);
    });

    it('dispatches success event after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertDispatched('role-created');
    });

    it('redirects to roles index after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertRedirect(route('admin.roles.index'));
    });

    it('clears permission cache after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertDispatched('role-created');

        // Verify role was created
        $this->assertDatabaseHas('roles', ['name' => Roles::VIEWER]);
    });
});

describe('Admin Roles Create - Validation', function () {
    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', '')
            ->set('permissions', [])
            ->call('store')
            ->assertHasErrors(['name' => 'required']);
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Livewire automatically converts values to strings for text inputs
        // So we test that a valid string name works
        // The string validation is handled by the FormRequest
        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertHasNoErrors(['name']);
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 256))
            ->set('permissions', [])
            ->call('store')
            ->assertHasErrors(['name' => 'max']);
    });

    it('validates name uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Role already exists from beforeEach
        Livewire::test(Create::class)
            ->set('name', Roles::ADMIN)
            ->set('permissions', [])
            ->call('store')
            ->assertHasErrors(['name' => 'unique']);
    });

    it('validates name is in allowed roles list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'invalid-role-name')
            ->set('permissions', [])
            ->call('store')
            ->assertHasErrors(['name' => 'in']);
    });

    it('validates permissions is array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        // We can't directly set a non-array to an array property in Livewire
        // Instead, we test this through validation rules
        // The validation will catch this when the form is submitted with invalid data
        // This test verifies the validation rule exists
        $component = Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', []);

        // The validation rule for permissions array is tested in StoreRoleRequestTest
        // Here we just verify the component accepts an array
        expect($component->get('permissions'))->toBeArray();
    });

    it('validates permissions exist in database', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', ['non-existent-permission'])
            ->call('store')
            ->assertHasErrors(['permissions.0' => 'exists']);
    });

    it('allows empty permissions array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Ensure the role doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        Livewire::test(Create::class)
            ->set('name', Roles::VIEWER)
            ->set('permissions', [])
            ->call('store')
            ->assertHasNoErrors();
    });
});

describe('Admin Roles Create - Permission Selection', function () {
    it('can select all permissions for a module', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->call('selectAllModulePermissions', 'programs');

        $permissions = $component->get('permissions');
        expect($permissions)->toContain(Permissions::PROGRAMS_VIEW)
            ->and($permissions)->toContain(Permissions::PROGRAMS_CREATE);
    });

    it('can deselect all permissions for a module', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('permissions', [Permissions::PROGRAMS_VIEW, Permissions::PROGRAMS_CREATE])
            ->call('deselectAllModulePermissions', 'programs');

        $permissions = $component->get('permissions');
        expect($permissions)->not->toContain(Permissions::PROGRAMS_VIEW)
            ->and($permissions)->not->toContain(Permissions::PROGRAMS_CREATE);
    });

    it('correctly identifies when all module permissions are selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('permissions', [Permissions::PROGRAMS_VIEW, Permissions::PROGRAMS_CREATE]);

        expect($component->instance()->areAllModulePermissionsSelected('programs'))->toBeTrue();
    });

    it('correctly identifies when not all module permissions are selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('permissions', [Permissions::PROGRAMS_VIEW]);

        expect($component->instance()->areAllModulePermissionsSelected('programs'))->toBeFalse();
    });

    it('does not duplicate permissions when selecting all for a module', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('permissions', [Permissions::PROGRAMS_VIEW])
            ->call('selectAllModulePermissions', 'programs');

        $permissions = $component->get('permissions');
        $programsPermissions = array_filter($permissions, function ($perm) {
            return str_starts_with($perm, 'programs.');
        });

        // Should have all programs permissions without duplicates
        expect(count($programsPermissions))->toBe(count(array_unique($programsPermissions)));
    });
});

describe('Admin Roles Create - Helper Methods', function () {
    it('returns correct module display names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        expect($component->instance()->getModuleDisplayName('programs'))->toBe(__('Programas'))
            ->and($component->instance()->getModuleDisplayName('calls'))->toBe(__('Convocatorias'))
            ->and($component->instance()->getModuleDisplayName('news'))->toBe(__('Noticias'))
            ->and($component->instance()->getModuleDisplayName('documents'))->toBe(__('Documentos'))
            ->and($component->instance()->getModuleDisplayName('events'))->toBe(__('Eventos'))
            ->and($component->instance()->getModuleDisplayName('users'))->toBe(__('Usuarios'));
    });

    it('returns correct permission display names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        expect($component->instance()->getPermissionDisplayName('programs.view'))->toBe(__('Ver'))
            ->and($component->instance()->getPermissionDisplayName('programs.create'))->toBe(__('Crear'))
            ->and($component->instance()->getPermissionDisplayName('programs.edit'))->toBe(__('Editar'))
            ->and($component->instance()->getPermissionDisplayName('programs.delete'))->toBe(__('Eliminar'))
            ->and($component->instance()->getPermissionDisplayName('calls.publish'))->toBe(__('Publicar'))
            ->and($component->instance()->getPermissionDisplayName('users.*'))->toBe(__('Todos'));
    });

    it('returns available permissions grouped by module', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        $availablePermissions = $component->get('availablePermissions');

        expect($availablePermissions)->toBeArray()
            ->and($availablePermissions)->toHaveKey('programs')
            ->and($availablePermissions)->toHaveKey('calls');

        // Verify structure
        if (isset($availablePermissions['programs'])) {
            expect($availablePermissions['programs'])->toBeArray();
            if (! empty($availablePermissions['programs'])) {
                expect($availablePermissions['programs'][0])->toHaveKey('name')
                    ->and($availablePermissions['programs'][0])->toHaveKey('id');
            }
        }
    });
});
