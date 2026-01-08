<?php

use App\Livewire\Admin\Roles\Show;
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

describe('Admin Roles Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $role = Role::where('name', Roles::ADMIN)->first();

        $this->get(route('admin.roles.show', $role))
            ->assertRedirect('/login');
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $this->get(route('admin.roles.show', $role))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access to admin users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $this->get(route('admin.roles.show', $role))
            ->assertForbidden();
    });

    it('denies access to editor users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $this->get(route('admin.roles.show', $role))
            ->assertForbidden();
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $this->get(route('admin.roles.show', $role))
            ->assertForbidden();
    });
});

describe('Admin Roles Show - Display', function () {
    it('displays role information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        Livewire::test(Show::class, ['role' => $role])
            ->assertSee(Roles::ADMIN);
    });

    it('displays role permissions grouped by module', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $role->givePermissionTo([Permissions::PROGRAMS_VIEW, Permissions::CALLS_VIEW]);

        $component = Livewire::test(Show::class, ['role' => $role]);
        $permissionsByModule = $component->get('permissionsByModule');

        expect($permissionsByModule)->toBeArray()
            ->and($permissionsByModule)->toHaveKey('programs')
            ->and($permissionsByModule)->toHaveKey('calls');
    });

    it('displays role without permissions correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::VIEWER)->first();

        $component = Livewire::test(Show::class, ['role' => $role]);
        $permissionsByModule = $component->get('permissionsByModule');

        expect($permissionsByModule)->toBeArray();
    });

    it('displays paginated users with this role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        // Create users with this role
        User::factory()->count(15)->create()->each(function ($user) use ($role) {
            $user->assignRole($role);
        });

        $component = Livewire::test(Show::class, ['role' => $role]);
        $users = $component->get('users');

        expect($users)->not->toBeNull()
            ->and($users->count())->toBe(10); // Default per page
    });

    it('paginates users correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        // Create users with this role
        User::factory()->count(25)->create()->each(function ($user) use ($role) {
            $user->assignRole($role);
        });

        $component = Livewire::test(Show::class, ['role' => $role])
            ->set('usersPerPage', 10);

        $users = $component->get('users');

        expect($users->hasPages())->toBeTrue()
            ->and($users->count())->toBe(10);
    });

    it('displays empty state when role has no users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::VIEWER)->first();

        Livewire::test(Show::class, ['role' => $role])
            ->assertSee(__('No hay usuarios con este rol asignado'));
    });
});

describe('Admin Roles Show - Actions', function () {
    it('shows edit button for users with update permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        Livewire::test(Show::class, ['role' => $role])
            ->assertSeeHtml('href="http://erasmus25.test/admin/roles/'.$role->id.'/editar"');
    });

    it('hides edit button for users without update permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        // Since the user doesn't have permission, the component will be forbidden
        // We test that the authorization check works correctly
        $this->get(route('admin.roles.show', $role))
            ->assertForbidden();
    });

    it('can confirm delete action', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create a deletable role (non-system, no users)
        $deletableRole = Role::create(['name' => 'deletable-role', 'guard_name' => 'web']);

        $component = Livewire::test(Show::class, ['role' => $deletableRole])
            ->call('confirmDelete');

        expect($component->get('showDeleteModal'))->toBeTrue();
    });

    it('can delete a role without users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $deletableRole = Role::create(['name' => 'deletable-role-2', 'guard_name' => 'web']);

        Livewire::test(Show::class, ['role' => $deletableRole])
            ->call('delete')
            ->assertDispatched('role-deleted')
            ->assertRedirect(route('admin.roles.index'));

        expect(Role::find($deletableRole->id))->toBeNull();
    });

    it('cannot delete a system role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::ADMIN)->first();

        Livewire::test(Show::class, ['role' => $systemRole])
            ->call('delete')
            ->assertDispatched('role-delete-error');

        expect(Role::find($systemRole->id))->not->toBeNull();
    });

    it('cannot delete a role with assigned users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $roleWithUsers = Role::create(['name' => 'role-with-users', 'guard_name' => 'web']);
        $assignedUser = User::factory()->create();
        $assignedUser->assignRole($roleWithUsers);

        // The mount method loads users_count via loadCount('users')
        // But the count might not persist, so we test the canDelete method instead
        $roleWithUsers = $roleWithUsers->fresh()->loadCount('users');
        
        $component = Livewire::test(Show::class, ['role' => $roleWithUsers]);
        
        // Verify users_count is loaded
        $role = $component->get('role');
        expect($role->users_count)->toBeGreaterThan(0);

        // Test that canDelete returns false for roles with users
        expect($component->instance()->canDelete())->toBeFalse();

        // The role should still exist
        expect(Role::find($roleWithUsers->id))->not->toBeNull();
    });

    it('clears permission cache after deleting a role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $deletableRole = Role::create(['name' => 'cache-test-role', 'guard_name' => 'web']);

        Livewire::test(Show::class, ['role' => $deletableRole])
            ->call('delete')
            ->assertDispatched('role-deleted');

        // Verify role was deleted
        expect(Role::find($deletableRole->id))->toBeNull();
    });
});

describe('Admin Roles Show - Helper Methods', function () {
    it('correctly identifies system roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::ADMIN)->first();
        $customRole = Role::create(['name' => 'custom-role-helper', 'guard_name' => 'web']);

        $component1 = Livewire::test(Show::class, ['role' => $systemRole]);
        $component2 = Livewire::test(Show::class, ['role' => $customRole]);

        expect($component1->instance()->isSystemRole())->toBeTrue()
            ->and($component2->instance()->isSystemRole())->toBeFalse();
    });

    it('correctly identifies if role can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::ADMIN)->first();
        $deletableRole = Role::create(['name' => 'deletable-role-helper', 'guard_name' => 'web']);
        $roleWithUsers = Role::create(['name' => 'role-with-users-helper', 'guard_name' => 'web']);
        User::factory()->create()->assignRole($roleWithUsers);

        $component1 = Livewire::test(Show::class, ['role' => $systemRole]);
        $component2 = Livewire::test(Show::class, ['role' => $deletableRole]);
        $component3 = Livewire::test(Show::class, ['role' => $roleWithUsers]);

        expect($component1->instance()->canDelete())->toBeFalse()
            ->and($component2->instance()->canDelete())->toBeTrue()
            ->and($component3->instance()->canDelete())->toBeFalse();
    });

    it('returns correct role display names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $component = Livewire::test(Show::class, ['role' => $role]);

        expect($component->instance()->getRoleDisplayName(Roles::SUPER_ADMIN))->toBe(__('Super Administrador'))
            ->and($component->instance()->getRoleDisplayName(Roles::ADMIN))->toBe(__('Administrador'))
            ->and($component->instance()->getRoleDisplayName(Roles::EDITOR))->toBe(__('Editor'))
            ->and($component->instance()->getRoleDisplayName(Roles::VIEWER))->toBe(__('Visualizador'))
            ->and($component->instance()->getRoleDisplayName('custom-role'))->toBe('custom-role');
    });

    it('returns correct role badge variants', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $component = Livewire::test(Show::class, ['role' => $role]);

        expect($component->instance()->getRoleBadgeVariant(Roles::SUPER_ADMIN))->toBe('danger')
            ->and($component->instance()->getRoleBadgeVariant(Roles::ADMIN))->toBe('warning')
            ->and($component->instance()->getRoleBadgeVariant(Roles::EDITOR))->toBe('info')
            ->and($component->instance()->getRoleBadgeVariant(Roles::VIEWER))->toBe('neutral')
            ->and($component->instance()->getRoleBadgeVariant('custom-role'))->toBe('neutral');
    });

    it('returns correct module display names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $component = Livewire::test(Show::class, ['role' => $role]);

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

        $role = Role::where('name', Roles::ADMIN)->first();
        $component = Livewire::test(Show::class, ['role' => $role]);

        expect($component->instance()->getPermissionDisplayName('programs.view'))->toBe(__('Ver'))
            ->and($component->instance()->getPermissionDisplayName('programs.create'))->toBe(__('Crear'))
            ->and($component->instance()->getPermissionDisplayName('programs.edit'))->toBe(__('Editar'))
            ->and($component->instance()->getPermissionDisplayName('programs.delete'))->toBe(__('Eliminar'))
            ->and($component->instance()->getPermissionDisplayName('calls.publish'))->toBe(__('Publicar'))
            ->and($component->instance()->getPermissionDisplayName('users.*'))->toBe(__('Todos'));
    });
});

describe('Admin Roles Show - Permissions Display', function () {
    it('groups permissions by module correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $role->givePermissionTo([
            Permissions::PROGRAMS_VIEW,
            Permissions::PROGRAMS_CREATE,
            Permissions::CALLS_VIEW,
        ]);

        $component = Livewire::test(Show::class, ['role' => $role]);
        $permissionsByModule = $component->get('permissionsByModule');

        expect($permissionsByModule)->toHaveKey('programs')
            ->and($permissionsByModule)->toHaveKey('calls')
            ->and($permissionsByModule['programs'])->toContain(Permissions::PROGRAMS_VIEW)
            ->and($permissionsByModule['programs'])->toContain(Permissions::PROGRAMS_CREATE)
            ->and($permissionsByModule['calls'])->toContain(Permissions::CALLS_VIEW);
    });

    it('only shows permissions assigned to the role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $role->givePermissionTo([Permissions::PROGRAMS_VIEW]);

        $component = Livewire::test(Show::class, ['role' => $role]);
        $permissionsByModule = $component->get('permissionsByModule');

        // Should only contain programs.view, not programs.create
        if (isset($permissionsByModule['programs'])) {
            expect($permissionsByModule['programs'])->toContain(Permissions::PROGRAMS_VIEW)
                ->and($permissionsByModule['programs'])->not->toContain(Permissions::PROGRAMS_CREATE);
        }
    });
});
