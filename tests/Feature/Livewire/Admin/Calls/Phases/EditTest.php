<?php

use App\Livewire\Admin\Calls\Phases\Edit;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
    ]);

    // Editor puede ver, crear y editar pero no eliminar
    $editor->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::CALLS_VIEW,
    ]);
});

describe('Admin Calls Phases Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $this->get(route('admin.calls.phases.edit', [$call, $phase]))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $this->get(route('admin.calls.phases.edit', [$call, $phase]))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without edit permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $this->get(route('admin.calls.phases.edit', [$call, $phase]))
            ->assertForbidden();
    });
});

describe('Admin Calls Phases Edit - Successful Update', function () {
    it('can update a phase with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Original',
            'phase_type' => 'publicacion',
        ]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('name', 'Fase Actualizada')
            ->set('description', 'Nueva descripción')
            ->set('phase_type', 'solicitudes')
            ->set('start_date', '2024-02-01')
            ->set('end_date', '2024-02-28')
            ->call('update')
            ->assertDispatched('phase-updated')
            ->assertRedirect(route('admin.calls.phases.index', $call));

        $this->assertDatabaseHas('call_phases', [
            'id' => $phase->id,
            'name' => 'Fase Actualizada',
            'description' => 'Nueva descripción',
            'phase_type' => 'solicitudes',
        ]);
    });

    it('marks phase as current and unmarks others', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $existingPhase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => true,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('is_current', true)
            ->call('update');

        expect($existingPhase->fresh()->is_current)->toBeFalse();
        expect($phase->fresh()->is_current)->toBeTrue();
    });

    it('can update order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 1,
        ]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('order', 5)
            ->call('update');

        expect($phase->fresh()->order)->toBe(5);
    });
});

describe('Admin Calls Phases Edit - Validation', function () {
    it('requires phase_type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('phase_type', '')
            ->set('name', 'Fase Test')
            ->call('update')
            ->assertHasErrors(['phase_type']);
    });

    it('requires name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('phase_type', 'publicacion')
            ->set('name', '')
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates phase_type is in allowed values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('phase_type', 'invalid_type')
            ->set('name', 'Fase Test')
            ->call('update')
            ->assertHasErrors(['phase_type']);
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('phase_type', 'publicacion')
            ->set('name', 'Fase Test')
            ->set('start_date', '2024-01-31')
            ->set('end_date', '2024-01-01')
            ->call('update')
            ->assertHasErrors(['end_date']);
    });

    it('validates order is unique per call (ignoring current phase)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase1 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 5,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 3,
        ]);

        // Should be able to update phase2 with order 3 (its current order)
        Livewire::test(Edit::class, ['call' => $call, 'phase' => $phase2])
            ->set('phase_type', 'publicacion')
            ->set('name', 'Fase Test')
            ->set('order', 3) // Same order as current
            ->call('update');

        // But should fail if trying to use order 5 (already taken by phase1)
        Livewire::test(Edit::class, ['call' => $call, 'phase' => $phase2])
            ->set('phase_type', 'publicacion')
            ->set('name', 'Fase Test')
            ->set('order', 5) // Already taken
            ->call('update');

        // Verify phase2 still has order 3
        expect($phase2->fresh()->order)->toBe(3);
    });

    it('validates only one phase can be current per call (ignoring current phase)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $currentPhase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => true,
        ]);

        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        // Should be able to mark phase as current (will unmark currentPhase)
        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->set('phase_type', 'publicacion')
            ->set('name', 'Fase Test')
            ->set('is_current', true)
            ->call('update');

        expect($currentPhase->fresh()->is_current)->toBeFalse();
        expect($phase->fresh()->is_current)->toBeTrue();
    });
});

describe('Admin Calls Phases Edit - Loading Data', function () {
    it('loads existing phase data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Test',
            'phase_type' => 'provisional',
            'description' => 'Descripción original',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'is_current' => true,
            'order' => 5,
        ]);

        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phase])
            ->assertSet('name', 'Fase Test')
            ->assertSet('phase_type', 'provisional')
            ->assertSet('description', 'Descripción original')
            ->assertSet('is_current', true)
            ->assertSet('order', 5);
    });
});

describe('Admin Calls Phases Edit - Date Overlap Detection', function () {
    it('dispatches warning when dates overlap with other phases', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Create an existing phase with dates
        CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Existente',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Create the phase to edit
        $phaseToEdit = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase a Editar',
            'start_date' => '2024-03-01',
            'end_date' => '2024-03-31',
        ]);

        // Update with overlapping dates
        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phaseToEdit])
            ->set('start_date', '2024-01-15')
            ->set('end_date', '2024-02-15')
            ->assertDispatched('phase-date-overlap-warning');
    });

    it('does not dispatch warning when dates do not overlap with other phases', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Create an existing phase with dates
        CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Existente',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);

        // Create the phase to edit
        $phaseToEdit = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase a Editar',
            'start_date' => '2024-03-01',
            'end_date' => '2024-03-31',
        ]);

        // Update with non-overlapping dates
        Livewire::test(Edit::class, ['call' => $call, 'call_phase' => $phaseToEdit])
            ->set('start_date', '2024-02-01')
            ->set('end_date', '2024-02-28')
            ->assertNotDispatched('phase-date-overlap-warning');
    });
});
