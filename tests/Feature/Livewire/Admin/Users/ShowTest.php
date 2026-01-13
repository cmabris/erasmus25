<?php

use App\Livewire\Admin\Users\Show;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Activitylog\Models\Activity;
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

describe('Admin Users Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $testUser = User::factory()->create();

        $this->get(route('admin.users.show', $testUser))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with users.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.show', $testUser))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows super-admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.show', $testUser))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows users to view their own profile', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.users.show', $user))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access to users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $this->get(route('admin.users.show', $testUser))
            ->assertForbidden();
    });
});

describe('Admin Users Show - Display', function () {
    it('displays user details correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create([
            'name' => 'Juan Pérez',
            'email' => 'juan@example.com',
        ]);

        Livewire::test(Show::class, ['user' => $testUser])
            ->assertSee('Juan Pérez')
            ->assertSee('juan@example.com');
    });

    it('displays user roles correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole([Roles::ADMIN, Roles::EDITOR]);

        $component = Livewire::test(Show::class, ['user' => $testUser->load('roles')]);
        $roles = $component->get('user')->roles;

        expect($roles->pluck('name')->toArray())->toContain(Roles::ADMIN)
            ->and($roles->pluck('name')->toArray())->toContain(Roles::EDITOR);
    });

    it('displays user permissions correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->givePermissionTo(Permissions::USERS_VIEW);

        $component = Livewire::test(Show::class, ['user' => $testUser->load('permissions')]);
        $permissions = $component->get('user')->permissions;

        expect($permissions->pluck('name')->toArray())->toContain(Permissions::USERS_VIEW);
    });

    it('displays audit logs count', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create 5 activities for the test user
        for ($i = 0; $i < 5; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($testUser)
                ->log('created');
        }

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $statistics = $component->get('statistics');

        expect($statistics['total_actions'])->toBe(5);
    });

    it('displays paginated audit logs', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create 15 activities for the test user
        for ($i = 0; $i < 15; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($testUser)
                ->log('created');
        }

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $activities = $component->get('activities');

        expect($activities->count())->toBe(10) // Default per page
            ->and($activities->total())->toBe(15);
    });

    it('can change audit logs per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create 25 activities for the test user
        for ($i = 0; $i < 25; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($testUser)
                ->log('created');
        }

        $component = Livewire::test(Show::class, ['user' => $testUser])
            ->set('activitiesPerPage', 20);

        $activities = $component->get('activities');
        expect($activities->count())->toBe(20);
    });

    it('displays statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create 3 activities with 'created' description
        for ($i = 0; $i < 3; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($testUser)
                ->log('created');
        }

        // Create 2 activities with 'updated' description
        for ($i = 0; $i < 2; $i++) {
            activity()
                ->performedOn($program)
                ->causedBy($testUser)
                ->log('updated');
        }

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $statistics = $component->get('statistics');

        expect($statistics['total_actions'])->toBe(5)
            ->and($statistics['actions_by_type']['created'])->toBe(3)
            ->and($statistics['actions_by_type']['updated'])->toBe(2);
    });

    it('displays last activity correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create old activity (5 days ago)
        $oldActivity = activity()
            ->performedOn($program)
            ->causedBy($testUser)
            ->log('created');
        $oldActivity->created_at = now()->subDays(5);
        $oldActivity->save();

        // Create new activity (now)
        $newActivity = activity()
            ->performedOn($program)
            ->causedBy($testUser)
            ->log('updated');

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $statistics = $component->get('statistics');

        expect($statistics['last_activity']->format('Y-m-d H:i:s'))->toBe($newActivity->created_at->format('Y-m-d H:i:s'));
    });
});

describe('Admin Users Show - Actions', function () {
    it('can delete a user (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('user-deleted')
            ->assertRedirect(route('admin.users.index'));

        expect($testUser->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete themselves', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        Livewire::test(Show::class, ['user' => $user])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('user-delete-error');

        expect($user->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->delete();

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('showRestoreModal', true)
            ->call('restore')
            ->assertDispatched('user-restored');

        expect($testUser->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a user (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->delete();

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('user-force-deleted')
            ->assertRedirect(route('admin.users.index'));

        expect(User::withTrashed()->find($testUser->id))->toBeNull();
    });

    it('cannot force delete themselves', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $user->delete();

        Livewire::test(Show::class, ['user' => $user])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('user-force-delete-error');

        expect(User::withTrashed()->find($user->id))->not->toBeNull();
    });
});

describe('Admin Users Show - Role Assignment', function () {
    it('can open assign roles modal', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole(Roles::VIEWER);

        $component = Livewire::test(Show::class, ['user' => $testUser])
            ->call('openAssignRolesModal');

        expect($component->get('showAssignRolesModal'))->toBeTrue()
            ->and($component->get('selectedRoles'))->toContain(Roles::VIEWER);
    });

    it('can assign roles to a user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $testUser->assignRole(Roles::VIEWER);

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('selectedRoles', [Roles::ADMIN, Roles::EDITOR])
            ->call('assignRoles')
            ->assertDispatched('user-roles-updated');

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

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('selectedRoles', [])
            ->call('assignRoles')
            ->assertDispatched('user-roles-updated');

        expect($testUser->fresh()->roles)->toBeEmpty();
    });

    it('cannot assign roles to themselves', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Show::class, ['user' => $user]);
        expect($component->instance()->canAssignRoles())->toBeFalse();
    });

    it('validates roles when assigning', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        Livewire::test(Show::class, ['user' => $testUser])
            ->set('selectedRoles', ['invalid-role'])
            ->call('assignRoles')
            ->assertHasErrors(['roles.0']);
    });
});

describe('Admin Users Show - Helpers', function () {
    it('can get role display name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->getRoleDisplayName(Roles::ADMIN))->toBe('Administrador')
            ->and($component->instance()->getRoleDisplayName(Roles::EDITOR))->toBe('Editor')
            ->and($component->instance()->getRoleDisplayName(Roles::VIEWER))->toBe('Visualizador');
    });

    it('can get role description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $description = $component->instance()->getRoleDescription(Roles::ADMIN);
        expect($description)->not->toBeEmpty();
    });

    it('can get role badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->getRoleBadgeVariant(Roles::SUPER_ADMIN))->toBe('danger')
            ->and($component->instance()->getRoleBadgeVariant(Roles::ADMIN))->toBe('warning')
            ->and($component->instance()->getRoleBadgeVariant(Roles::EDITOR))->toBe('info')
            ->and($component->instance()->getRoleBadgeVariant(Roles::VIEWER))->toBe('neutral');
    });

    it('can get action display name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->getActionDisplayName('create'))->toBe('Crear')
            ->and($component->instance()->getActionDisplayName('update'))->toBe('Actualizar')
            ->and($component->instance()->getActionDisplayName('delete'))->toBe('Eliminar');
    });

    it('can get action badge variant', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->getActionBadgeVariant('create'))->toBe('success')
            ->and($component->instance()->getActionBadgeVariant('update'))->toBe('info')
            ->and($component->instance()->getActionBadgeVariant('delete'))->toBe('danger');
    });

    it('can get model display name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->getModelDisplayName('App\Models\Program'))->toBe('Programa')
            ->and($component->instance()->getModelDisplayName('App\Models\Call'))->toBe('Convocatoria')
            ->and($component->instance()->getModelDisplayName(null))->toBe('-');
    });

    it('can get model URL', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $url = $component->instance()->getModelUrl('App\Models\Program', $program->id);

        expect($url)->toBe(route('admin.programs.show', $program));
    });

    it('can get model title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create(['name' => 'Test Program']);

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $title = $component->instance()->getModelTitle($program);

        expect($title)->toBe('Test Program');
    });

    it('can format changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $changes = [
            'before' => ['name' => 'Old Name', 'email' => 'old@example.com'],
            'after' => ['name' => 'New Name', 'email' => 'new@example.com'],
        ];

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $formatted = $component->instance()->formatChanges($changes);

        expect($formatted)->toContain('name')
            ->and($formatted)->toContain('email');
    });

    it('can check if user can edit', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->canEdit())->toBeTrue();
    });

    it('can check if user can delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->canDelete())->toBeTrue();
    });

    it('can check if user can assign roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        expect($component->instance()->canAssignRoles())->toBeTrue();
    });
});

describe('Admin Users Show - Audit Logs Display', function () {
    it('displays audit logs with model relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create an activity for the test user on the program
        activity()
            ->performedOn($program)
            ->causedBy($testUser)
            ->log('created');

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $activities = $component->get('activities');

        expect($activities->first()->subject)->not->toBeNull()
            ->and($activities->first()->subject_type)->toBe(Program::class);
    });

    it('orders audit logs by created_at desc', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();
        $program = Program::factory()->create();

        // Create old activity (2 days ago)
        $oldActivity = activity()
            ->performedOn($program)
            ->causedBy($testUser)
            ->log('created');
        $oldActivity->created_at = now()->subDays(2);
        $oldActivity->save();

        // Create new activity (now)
        $newActivity = activity()
            ->performedOn($program)
            ->causedBy($testUser)
            ->log('updated');

        $component = Livewire::test(Show::class, ['user' => $testUser]);
        $activities = $component->get('activities');

        expect($activities->first()->id)->toBe($newActivity->id);
    });
});
