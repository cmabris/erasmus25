<?php

use App\Livewire\Admin\Users\Edit;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

describe('Admin Users Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $testUser = User::factory()->create();

        $this->get(route('admin.users.edit', $testUser))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with users.edit permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.edit', $testUser))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.edit', $testUser))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('allows users to edit their own profile', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.users.edit', $user))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.edit', $testUser))
            ->assertForbidden();
    });
});

describe('Admin Users Edit - Successful Update', function () {
    it('can update user with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create([
            'name' => 'Nombre Original',
            'email' => 'original@example.com',
        ]);

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', 'Nombre Actualizado')
            ->set('email', 'actualizado@example.com')
            ->call('update')
            ->assertDispatched('user-updated')
            ->assertRedirect(route('admin.users.index'));

        expect($testUser->fresh()->name)->toBe('Nombre Actualizado')
            ->and($testUser->fresh()->email)->toBe('actualizado@example.com');
    });

    it('loads existing user data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        $component = Livewire::test(Edit::class, ['user' => $testUser]);

        expect($component->get('name'))->toBe('Juan Pérez')
            ->and($component->get('email'))->toBe('juan@example.com');
    });

    it('loads existing roles correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole([Roles::ADMIN, Roles::EDITOR]);

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        $selectedRoles = $component->get('selectedRoles');

        expect($selectedRoles)->toContain(Roles::ADMIN)
            ->and($selectedRoles)->toContain(Roles::EDITOR);
    });

    it('dispatches success event after update', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', 'Nombre Actualizado')
            ->call('update')
            ->assertDispatched('user-updated');
    });

    it('redirects to users index after update', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', 'Nombre Actualizado')
            ->call('update')
            ->assertRedirect(route('admin.users.index'));
    });
});

describe('Admin Users Edit - Password Update', function () {
    it('can update password when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $oldPassword = $testUser->password;

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('update');

        expect(Hash::check('newpassword123', $testUser->fresh()->password))->toBeTrue()
            ->and($testUser->fresh()->password)->not->toBe($oldPassword);
    });

    it('does not update password when not provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $oldPassword = $testUser->password;

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', 'Nombre Actualizado')
            ->set('email', $testUser->email)
            ->set('password', '')
            ->set('password_confirmation', '')
            ->call('update');

        expect($testUser->fresh()->password)->toBe($oldPassword);
    });

    it('requires password confirmation when password is provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'differentpassword')
            ->call('update')
            ->assertHasErrors(['password']);
    });

    it('validates password minimum length when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('update')
            ->assertHasErrors(['password']);
    });
});

describe('Admin Users Edit - Role Update', function () {
    it('can update roles when user has permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole(Roles::VIEWER);

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('selectedRoles', [Roles::ADMIN, Roles::EDITOR])
            ->call('update');

        expect($testUser->fresh()->hasRole(Roles::ADMIN))->toBeTrue()
            ->and($testUser->fresh()->hasRole(Roles::EDITOR))->toBeTrue()
            ->and($testUser->fresh()->hasRole(Roles::VIEWER))->toBeFalse();
    });

    it('can remove all roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole([Roles::ADMIN, Roles::EDITOR]);

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('selectedRoles', [])
            ->call('update');

        expect($testUser->fresh()->roles)->toBeEmpty();
    });

    it('cannot modify own roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // User tries to edit themselves
        $component = Livewire::test(Edit::class, ['user' => $user]);

        expect($component->instance()->canAssignRoles())->toBeFalse();
    });

    it('does not update roles when user cannot assign roles', function () {
        // Create a user without USERS_EDIT permission (only USERS_VIEW)
        $viewerUser = User::factory()->create();
        $viewerUser->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($viewerUser);

        $testUser = User::factory()->create();
        $testUser->assignRole(Roles::ADMIN);

        // Viewer user cannot edit, so this should be forbidden
        // But if they could edit, they shouldn't be able to assign roles
        // Let's test with a user that can view but not edit
        $this->get(route('admin.users.edit', $testUser))
            ->assertForbidden();
    });

    it('allows users with USERS_EDIT to assign roles to other users', function () {
        $editorUser = User::factory()->create();
        $editorUser->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($editorUser);

        $testUser = User::factory()->create();
        $testUser->assignRole(Roles::ADMIN);

        // Editor user can edit and assign roles (USERS_EDIT includes assignRoles permission)
        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('selectedRoles', [Roles::EDITOR])
            ->call('update');

        // Roles should change because editor has USERS_EDIT permission
        expect($testUser->fresh()->hasRole(Roles::EDITOR))->toBeTrue()
            ->and($testUser->fresh()->hasRole(Roles::ADMIN))->toBeFalse();
    });

    it('validates roles are in allowed list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', $testUser->email)
            ->set('selectedRoles', ['invalid-role'])
            ->call('update')
            ->assertHasErrors(['roles.0']);
    });
});

describe('Admin Users Edit - Validation', function () {
    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', '')
            ->set('email', $testUser->email)
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', str_repeat('a', 256))
            ->set('email', $testUser->email)
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('requires email field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', '')
            ->call('update')
            ->assertHasErrors(['email']);
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', 'invalid-email')
            ->call('update')
            ->assertHasErrors(['email']);
    });

    it('validates unique email excluding current user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['email' => 'test@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        // Should allow keeping the same email
        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', 'test@example.com')
            ->call('update')
            ->assertHasNoErrors(['email']);

        // Should reject using another user's email
        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', 'other@example.com')
            ->call('update')
            ->assertHasErrors(['email']);
    });

    it('validates email max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Edit::class, ['user' => $testUser])
            ->set('name', $testUser->name)
            ->set('email', str_repeat('a', 250).'@example.com')
            ->call('update')
            ->assertHasErrors(['email']);
    });
});

describe('Admin Users Edit - Component Features', function () {
    it('displays available roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        $availableRoles = $component->get('roles');

        expect($availableRoles)->not->toBeEmpty()
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::ADMIN)
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::EDITOR)
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::VIEWER);
    });

    it('can get role display name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        expect($component->instance()->getRoleDisplayName(Roles::ADMIN))->toBe('Administrador')
            ->and($component->instance()->getRoleDisplayName(Roles::EDITOR))->toBe('Editor')
            ->and($component->instance()->getRoleDisplayName(Roles::VIEWER))->toBe('Visualizador');
    });

    it('can get role description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        $description = $component->instance()->getRoleDescription(Roles::ADMIN);
        expect($description)->not->toBeEmpty();
    });

    it('can get role badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        expect($component->instance()->getRoleBadgeVariant(Roles::SUPER_ADMIN))->toBe('danger')
            ->and($component->instance()->getRoleBadgeVariant(Roles::ADMIN))->toBe('warning')
            ->and($component->instance()->getRoleBadgeVariant(Roles::EDITOR))->toBe('info')
            ->and($component->instance()->getRoleBadgeVariant(Roles::VIEWER))->toBe('neutral');
    });

    it('can check if user can assign roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Edit::class, ['user' => $testUser]);
        expect($component->instance()->canAssignRoles())->toBeTrue();
    });

    it('cannot assign roles to itself', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Edit::class, ['user' => $user]);
        expect($component->instance()->canAssignRoles())->toBeFalse();
    });
});
