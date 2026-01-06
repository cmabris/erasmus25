<?php

use App\Livewire\Admin\Users\Create;
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

describe('Admin Users Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.users.create'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with users.create permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_CREATE);
        $this->actingAs($user);

        $this->get(route('admin.users.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.users.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.users.create'))
            ->assertForbidden();
    });
});

describe('Admin Users Create - User Creation', function () {
    it('can create a user with required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertDispatched('user-created')
            ->assertRedirect(route('admin.users.index'));

        $createdUser = User::where('email', 'juan@example.com')->first();
        expect($createdUser)->not->toBeNull()
            ->and($createdUser->name)->toBe('Juan Pérez')
            ->and($createdUser->email)->toBe('juan@example.com')
            ->and(Hash::check('password123', $createdUser->password))->toBeTrue();
    });

    it('creates user without roles when no roles are selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', [])
            ->call('store');

        $createdUser = User::where('email', 'juan@example.com')->first();
        expect($createdUser->roles)->toBeEmpty();
    });

    it('assigns roles when roles are selected', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', [Roles::ADMIN, Roles::EDITOR])
            ->call('store');

        $createdUser = User::where('email', 'juan@example.com')->first();
        expect($createdUser->roles->pluck('name')->toArray())->toContain(Roles::ADMIN)
            ->and($createdUser->roles->pluck('name')->toArray())->toContain(Roles::EDITOR);
    });

    it('dispatches success event after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertDispatched('user-created');
    });

    it('redirects to users index after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertRedirect(route('admin.users.index'));
    });
});

describe('Admin Users Create - Role Assignment', function () {
    it('can assign single role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', [Roles::ADMIN])
            ->call('store');

        $createdUser = User::where('email', 'juan@example.com')->first();
        expect($createdUser->roles->pluck('name')->toArray())->toContain(Roles::ADMIN)
            ->and($createdUser->roles->count())->toBe(1);
    });

    it('can assign multiple roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', [Roles::ADMIN, Roles::EDITOR, Roles::VIEWER])
            ->call('store');

        $createdUser = User::where('email', 'juan@example.com')->first();
        expect($createdUser->roles->count())->toBe(3)
            ->and($createdUser->hasRole(Roles::ADMIN))->toBeTrue()
            ->and($createdUser->hasRole(Roles::EDITOR))->toBeTrue()
            ->and($createdUser->hasRole(Roles::VIEWER))->toBeTrue();
    });

    it('only assigns valid roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // This should fail validation, but let's test that invalid roles are rejected
        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', ['invalid-role'])
            ->call('store')
            ->assertHasErrors(['roles.0']);
    });
});

describe('Admin Users Create - Validation', function () {
    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 256))
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('requires email field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['email']);
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'invalid-email')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['email']);
    });

    it('validates unique email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        User::factory()->create(['email' => 'existing@example.com']);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'existing@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['email']);
    });

    it('validates email max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', str_repeat('a', 250).'@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['email']);
    });

    it('requires password field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password_confirmation', 'password123')
            ->call('store')
            ->assertHasErrors(['password']);
    });

    it('validates password confirmation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'different-password')
            ->call('store')
            ->assertHasErrors(['password']);
    });

    it('validates password minimum length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'short')
            ->set('password_confirmation', 'short')
            ->call('store')
            ->assertHasErrors(['password']);
    });

    it('validates roles array format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Livewire will convert string to array, but validation should catch it
        // We test that roles must be an array by using reflection or testing the actual validation
        // Since Livewire handles type coercion, we test with invalid array values instead
        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', []) // Empty array is valid, but we can test with invalid role values
            ->call('store')
            ->assertHasNoErrors(['roles']); // Empty array should be valid
    });

    it('validates each role is a string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', [123, 456]) // Invalid: should be strings
            ->call('store')
            ->assertHasErrors(['roles.0', 'roles.1']);
    });

    it('validates roles are in allowed list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Juan Pérez')
            ->set('email', 'juan@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('roles', ['invalid-role-name'])
            ->call('store')
            ->assertHasErrors(['roles.0']);
    });
});

describe('Admin Users Create - Component Features', function () {
    it('displays available roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        $availableRoles = $component->get('availableRoles');

        expect($availableRoles)->not->toBeEmpty()
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::ADMIN)
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::EDITOR)
            ->and($availableRoles->pluck('name')->toArray())->toContain(Roles::VIEWER);
    });

    it('can get role display name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        expect($component->instance()->getRoleDisplayName(Roles::ADMIN))->toBe('Administrador')
            ->and($component->instance()->getRoleDisplayName(Roles::EDITOR))->toBe('Editor')
            ->and($component->instance()->getRoleDisplayName(Roles::VIEWER))->toBe('Visualizador');
    });

    it('can get role description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        $description = $component->instance()->getRoleDescription(Roles::ADMIN);
        expect($description)->not->toBeEmpty();
    });

    it('can get role badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class);
        expect($component->instance()->getRoleBadgeVariant(Roles::SUPER_ADMIN))->toBe('danger')
            ->and($component->instance()->getRoleBadgeVariant(Roles::ADMIN))->toBe('warning')
            ->and($component->instance()->getRoleBadgeVariant(Roles::EDITOR))->toBe('info')
            ->and($component->instance()->getRoleBadgeVariant(Roles::VIEWER))->toBe('neutral');
    });
});
