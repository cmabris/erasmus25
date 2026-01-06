<?php

use App\Livewire\Admin\Users\Index;
use App\Models\AuditLog;
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
    Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::USERS_VIEW,
        Permissions::USERS_CREATE,
        Permissions::USERS_EDIT,
        Permissions::USERS_DELETE,
    ]);
});

describe('Admin Users Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.users.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with users.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.users.index'))
            ->assertForbidden();
    });
});

describe('Admin Users Index - Listing', function () {
    it('displays all users by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $user1 = User::factory()->create(['name' => 'Juan Pérez', 'email' => 'juan@example.com']);
        $user2 = User::factory()->create(['name' => 'María García', 'email' => 'maria@example.com']);

        Livewire::test(Index::class)
            ->assertSee('Juan Pérez')
            ->assertSee('María García');
    });

    it('displays user information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);
        $testUser->assignRole(Roles::ADMIN);

        Livewire::test(Index::class)
            ->assertSee('Juan Pérez')
            ->assertSee('juan@example.com');
    });

    it('displays audit log counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        AuditLog::factory()->count(5)->create(['user_id' => $testUser->id]);

        Livewire::test(Index::class)
            ->assertSee('5');
    });
});

describe('Admin Users Index - Search', function () {
    it('can search users by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $user1 = User::factory()->create(['name' => 'Juan Pérez']);
        $user2 = User::factory()->create(['name' => 'María García']);

        $component = Livewire::test(Index::class)
            ->set('search', 'Juan');

        $users = $component->get('users');
        $userNames = $users->pluck('name')->toArray();
        expect($userNames)->toContain('Juan Pérez')
            ->and($userNames)->not->toContain('María García');
    });

    it('can search users by email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $user1 = User::factory()->create(['email' => 'juan@example.com']);
        $user2 = User::factory()->create(['email' => 'maria@example.com']);

        Livewire::test(Index::class)
            ->set('search', 'juan@example.com')
            ->assertSee('juan@example.com')
            ->assertDontSee('maria@example.com');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        expect($component->get('search'))->toBe('test');
        expect($component->get('users')->currentPage())->toBe(1);
    });
});

describe('Admin Users Index - Role Filter', function () {
    it('can filter users by role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $adminUser = User::factory()->create(['name' => 'Admin User']);
        $adminUser->assignRole(Roles::ADMIN);

        $editorUser = User::factory()->create(['name' => 'Editor User']);
        $editorUser->assignRole(Roles::EDITOR);

        $component = Livewire::test(Index::class)
            ->set('filterRole', Roles::ADMIN);

        $users = $component->get('users');
        $userNames = $users->pluck('name')->toArray();
        expect($userNames)->toContain('Admin User')
            ->and($userNames)->not->toContain('Editor User');
    });

    it('shows all users when no role filter is applied', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $adminUser = User::factory()->create(['name' => 'Admin User']);
        $adminUser->assignRole(Roles::ADMIN);

        $editorUser = User::factory()->create(['name' => 'Editor User']);
        $editorUser->assignRole(Roles::EDITOR);

        $component = Livewire::test(Index::class)
            ->set('filterRole', '');

        $users = $component->get('users');
        $userNames = $users->pluck('name')->toArray();
        expect($userNames)->toContain('Admin User')
            ->and($userNames)->toContain('Editor User');
    });
});

describe('Admin Users Index - Sorting', function () {
    it('can sort by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->create(['name' => 'Zeta User']);
        User::factory()->create(['name' => 'Alpha User']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'name');

        $component->assertSee('Alpha User')
            ->assertSee('Zeta User');

        $users = $component->get('users');
        $names = $users->pluck('name')->toArray();
        expect($names)->toContain('Alpha User')
            ->and($names)->toContain('Zeta User');
    });

    it('can sort by email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->create(['email' => 'zeta@example.com']);
        User::factory()->create(['email' => 'alpha@example.com']);

        Livewire::test(Index::class)
            ->call('sortBy', 'email')
            ->assertSee('alpha@example.com')
            ->assertSee('zeta@example.com');
    });

    it('can sort by created_at', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $oldUser = User::factory()->create(['created_at' => now()->subDays(5)]);
        $newUser = User::factory()->create(['created_at' => now()]);

        Livewire::test(Index::class)
            ->call('sortBy', 'created_at')
            ->assertSee($newUser->name)
            ->assertSee($oldUser->name);
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->create(['name' => 'Alpha']);
        User::factory()->create(['name' => 'Zeta']);

        $component = Livewire::test(Index::class);

        // El estado inicial es sortField='created_at' y sortDirection='desc'
        expect($component->get('sortField'))->toBe('created_at')
            ->and($component->get('sortDirection'))->toBe('desc');

        // Llamar a sortBy con 'name' cambia el campo
        $component->call('sortBy', 'name');

        expect($component->get('sortDirection'))->toBe('asc')
            ->and($component->get('sortField'))->toBe('name');

        // Llamar de nuevo al mismo campo cambia la dirección
        $component->call('sortBy', 'name');

        expect($component->get('sortDirection'))->toBe('desc')
            ->and($component->get('sortField'))->toBe('name');
    });
});

describe('Admin Users Index - Pagination', function () {
    it('paginates users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        expect($component->get('users')->hasPages())->toBeTrue();
        expect($component->get('users')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        // Total users: 20 created + 1 authenticated = 21, but perPage is 25, so all should be shown
        expect($component->get('users')->count())->toBe(21);
    });
});

describe('Admin Users Index - Soft Delete', function () {
    it('shows only non-deleted users by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $activeUser = User::factory()->create(['name' => 'Active User']);
        $deletedUser = User::factory()->create(['name' => 'Deleted User']);
        $deletedUser->delete();

        Livewire::test(Index::class)
            ->assertSee('Active User')
            ->assertDontSee('Deleted User');
    });

    it('can show deleted users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $activeUser = User::factory()->create(['name' => 'Active User']);
        $deletedUser = User::factory()->create(['name' => 'Deleted User']);
        $deletedUser->delete();

        $component = Livewire::test(Index::class)
            ->set('showDeleted', '1');

        $users = $component->get('users');
        $userNames = $users->pluck('name')->toArray();
        expect($userNames)->toContain('Deleted User')
            ->and($userNames)->not->toContain('Active User');
    });

    it('can delete a user (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Test User']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $testUser->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('userToDelete', $testUser->id)
            ->call('delete')
            ->assertDispatched('user-deleted');

        expect($testUser->fresh()->trashed())->toBeTrue();
    });

    it('can delete a user even with audit logs (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Test User']);
        AuditLog::factory()->create(['user_id' => $testUser->id]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $testUser->id)
            ->call('delete')
            ->assertDispatched('user-deleted');

        expect($testUser->fresh()->trashed())->toBeTrue();
    });

    it('can restore a deleted user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Test User']);
        $testUser->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $testUser->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('userToRestore', $testUser->id)
            ->call('restore')
            ->assertDispatched('user-restored');

        expect($testUser->fresh()->trashed())->toBeFalse();
    });

    it('cannot delete itself', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->call('confirmDelete', $user->id)
            ->call('delete')
            ->assertDispatched('user-delete-error');

        expect($user->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a user without audit logs (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Test User']);
        $testUser->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $testUser->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('userToForceDelete', $testUser->id)
            ->call('forceDelete')
            ->assertDispatched('user-force-deleted');

        expect(User::withTrashed()->find($testUser->id))->toBeNull();
    });

    it('can force delete a user with audit logs (sets user_id to null)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['name' => 'Test User']);
        $auditLog = AuditLog::factory()->create(['user_id' => $testUser->id]);
        $testUser->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $testUser->id)
            ->call('forceDelete')
            ->assertDispatched('user-force-deleted');

        expect(User::withTrashed()->find($testUser->id))->toBeNull();
        expect($auditLog->fresh()->user_id)->toBeNull();
    });

    it('cannot force delete itself', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $user->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $user->id)
            ->call('forceDelete')
            ->assertDispatched('user-force-delete-error');

        expect(User::withTrashed()->find($user->id))->not->toBeNull();
    });
});

describe('Admin Users Index - Helpers', function () {
    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('filterRole', Roles::ADMIN)
            ->set('showDeleted', '1')
            ->call('resetFilters');

        expect($component->get('search'))->toBe('')
            ->and($component->get('filterRole'))->toBe('')
            ->and($component->get('showDeleted'))->toBe('0');
    });

    it('can check if user can create', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Verificar indirectamente que el botón de crear está visible
        Livewire::test(Index::class)
            ->assertSee('Crear Usuario');
    });

    it('can check if user can view deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canViewDeleted())->toBeTrue();
    });

    it('can check if user can delete another user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteUser($testUser))->toBeTrue();
    });

    it('cannot delete itself', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteUser($user))->toBeFalse();
    });
});
