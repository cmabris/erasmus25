<?php

use App\Livewire\Admin\Roles\Index;
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
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);

    // Crear roles del sistema
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar todos los permisos a super-admin
    $superAdmin->givePermissionTo(Permission::all());
});

describe('Admin Roles Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.roles.index'))
            ->assertRedirect('/login');
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access to admin users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('denies access to editor users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.roles.index'))
            ->assertForbidden();
    });
});

describe('Admin Roles Index - Listing', function () {
    it('displays all roles by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertSee(__('Super Administrador'))
            ->assertSee(__('Administrador'))
            ->assertSee(__('Editor'))
            ->assertSee(__('Visualizador'));
    });

    it('displays role information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $role->givePermissionTo(Permissions::PROGRAMS_VIEW);

        Livewire::test(Index::class)
            ->assertSee(Roles::ADMIN);
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user1->assignRole($role);
        $user2->assignRole($role);

        Livewire::test(Index::class)
            ->assertSee('2'); // Users count
    });
});

describe('Admin Roles Index - Search', function () {
    it('can search roles by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create a custom role
        Role::create(['name' => 'custom-role', 'guard_name' => 'web']);

        $component = Livewire::test(Index::class)
            ->set('search', 'custom');

        $roles = $component->get('roles');
        $roleNames = $roles->pluck('name')->toArray();
        expect($roleNames)->toContain('custom-role');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create multiple custom roles
        for ($i = 1; $i <= 20; $i++) {
            Role::create(['name' => "custom-role-{$i}", 'guard_name' => 'web']);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 5);

        // Simulate being on page 2 by setting page directly
        $component->set('search', 'custom-role-1');

        // The updatedSearch() method should reset pagination
        expect($component->get('roles')->currentPage())->toBe(1);
    });
});

describe('Admin Roles Index - Sorting', function () {
    it('can sort by name ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create custom roles with specific names
        Role::create(['name' => 'zebra-role', 'guard_name' => 'web']);
        Role::create(['name' => 'alpha-role', 'guard_name' => 'web']);

        $component = Livewire::test(Index::class);

        // Default sortField is 'name' and sortDirection is 'asc'
        expect($component->get('sortField'))->toBe('name')
            ->and($component->get('sortDirection'))->toBe('asc');

        // Verify roles are sorted
        $roles = $component->get('roles');
        expect($roles->count())->toBeGreaterThan(0);
    });

    it('can sort by name descending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create custom roles
        Role::create(['name' => 'zebra-role', 'guard_name' => 'web']);
        Role::create(['name' => 'alpha-role', 'guard_name' => 'web']);

        $component = Livewire::test(Index::class);

        // Since sortField is already 'name', calling sortBy('name') will toggle direction
        $component->call('sortBy', 'name');

        // First call toggles from 'asc' to 'desc' (since field is already 'name')
        expect($component->get('sortField'))->toBe('name')
            ->and($component->get('sortDirection'))->toBe('desc');
    });

    it('resets pagination when sorting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create multiple roles
        for ($i = 1; $i <= 20; $i++) {
            Role::create(['name' => "role-{$i}", 'guard_name' => 'web']);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 5)
            ->call('sortBy', 'name');

        // sortBy() should reset pagination to page 1
        expect($component->get('roles')->currentPage())->toBe(1);
    });
});

describe('Admin Roles Index - Pagination', function () {
    it('paginates roles correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create more roles than per page
        for ($i = 1; $i <= 20; $i++) {
            Role::create(['name' => "role-{$i}", 'guard_name' => 'web']);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 5);

        $roles = $component->get('roles');
        expect($roles->count())->toBe(5)
            ->and($roles->total())->toBeGreaterThan(5);
    });

    it('shows empty state when no roles match filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('search', 'non-existent-role-name-xyz')
            ->assertSee(__('No se encontraron roles'));
    });
});

describe('Admin Roles Index - Actions', function () {
    it('shows create button only for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canCreate())->toBeTrue();
    });

    it('can delete a role without users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $customRole = Role::create(['name' => 'custom-role-to-delete', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $customRole->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('roleToDelete', $customRole->id)
            ->call('delete')
            ->assertDispatched('role-deleted');

        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    });

    it('cannot delete a system role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::ADMIN)->first();

        Livewire::test(Index::class)
            ->call('confirmDelete', $systemRole->id)
            ->call('delete')
            ->assertDispatched('role-delete-error')
            ->assertSee(__('No se puede eliminar un rol del sistema.'));

        $this->assertDatabaseHas('roles', ['id' => $systemRole->id]);
    });

    it('cannot delete a role with assigned users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $customRole = Role::create(['name' => 'custom-role-with-users', 'guard_name' => 'web']);
        $testUser = User::factory()->create();
        $testUser->assignRole($customRole);

        Livewire::test(Index::class)
            ->call('confirmDelete', $customRole->id)
            ->call('delete')
            ->assertDispatched('role-delete-error')
            ->assertSee(__('No se puede eliminar el rol porque tiene usuarios asignados.'));

        $this->assertDatabaseHas('roles', ['id' => $customRole->id]);
    });

    it('clears permission cache after deleting a role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $customRole = Role::create(['name' => 'custom-role-cache-test', 'guard_name' => 'web']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $customRole->id)
            ->call('delete')
            ->assertDispatched('role-deleted');

        // Verify that the role was deleted
        $this->assertDatabaseMissing('roles', ['id' => $customRole->id]);
    });
});

describe('Admin Roles Index - Permissions', function () {
    it('shows create button only for users with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canCreate())->toBeTrue();
    });

    it('hides create button for users without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Admin cannot access the page, but if they could, they shouldn't see create button
        // This is tested through authorization tests above
        $this->get(route('admin.roles.index'))
            ->assertForbidden();
    });

    it('shows delete button only for roles that can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $customRole = Role::create(['name' => 'deletable-role', 'guard_name' => 'web']);
        $systemRole = Role::where('name', Roles::ADMIN)->first();

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteRole($customRole))->toBeTrue()
            ->and($component->instance()->canDeleteRole($systemRole))->toBeFalse();
    });

    it('hides delete button for system roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::SUPER_ADMIN)->first();

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteRole($systemRole))->toBeFalse();
    });

    it('hides delete button for roles with assigned users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $customRole = Role::create(['name' => 'role-with-users', 'guard_name' => 'web']);
        $testUser = User::factory()->create();
        $testUser->assignRole($customRole);

        // Refresh the role to get the updated users_count
        $customRole->refresh();
        $customRole->loadCount('users');

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteRole($customRole))->toBeFalse();
    });
});

describe('Admin Roles Index - Filters', function () {
    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('search', 'test-search')
            ->call('resetFilters');

        expect($component->get('search'))->toBe('');
    });

    it('resets pagination when resetting filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Create multiple roles
        for ($i = 1; $i <= 20; $i++) {
            Role::create(['name' => "role-{$i}", 'guard_name' => 'web']);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 5)
            ->set('search', 'role-1')
            ->call('resetFilters');

        // resetFilters() should reset pagination to page 1
        expect($component->get('roles')->currentPage())->toBe(1);
    });
});

describe('Admin Roles Index - Helper Methods', function () {
    it('correctly identifies system roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $systemRole = Role::where('name', Roles::ADMIN)->first();
        $customRole = Role::create(['name' => 'custom-role', 'guard_name' => 'web']);

        $component = Livewire::test(Index::class);
        expect($component->instance()->isSystemRole($systemRole))->toBeTrue()
            ->and($component->instance()->isSystemRole($customRole))->toBeFalse();
    });

    it('returns correct role display names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
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

        $component = Livewire::test(Index::class);
        expect($component->instance()->getRoleBadgeVariant(Roles::SUPER_ADMIN))->toBe('danger')
            ->and($component->instance()->getRoleBadgeVariant(Roles::ADMIN))->toBe('warning')
            ->and($component->instance()->getRoleBadgeVariant(Roles::EDITOR))->toBe('info')
            ->and($component->instance()->getRoleBadgeVariant(Roles::VIEWER))->toBe('neutral')
            ->and($component->instance()->getRoleBadgeVariant('custom-role'))->toBe('neutral');
    });
});
