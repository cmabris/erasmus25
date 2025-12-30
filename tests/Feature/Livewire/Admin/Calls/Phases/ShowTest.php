<?php

use App\Livewire\Admin\Calls\Phases\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
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

describe('Admin Calls Phases Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $this->get(route('admin.calls.phases.show', [$call, $phase]))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with view permission to access', function () {
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

        $this->get(route('admin.calls.phases.show', [$call, $phase]))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
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

        $this->get(route('admin.calls.phases.show', [$call, $phase]))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });
});

describe('Admin Calls Phases Show - Display', function () {
    it('displays phase information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
        ]);
        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Test',
            'phase_type' => 'publicacion',
            'description' => 'Descripción de prueba',
            'is_current' => true,
            'order' => 1,
        ]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->assertSee('Fase Test')
            ->assertSee('Publicación')
            ->assertSee('Descripción de prueba');
    });

    it('displays parent call information', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->assertSee('Convocatoria Test')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025');
    });

    it('displays related resolutions', function () {
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
        Resolution::factory()->count(3)->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->assertSee('3'); // resolutions count
    });
});

describe('Admin Calls Phases Show - Actions', function () {
    it('can mark phase as current', function () {
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
            'is_current' => false,
        ]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('markAsCurrent')
            ->assertDispatched('phase-updated');

        expect($phase->fresh()->is_current)->toBeTrue();
    });

    it('can unmark phase as current', function () {
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
            'is_current' => true,
        ]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('unmarkAsCurrent')
            ->assertDispatched('phase-updated');

        expect($phase->fresh()->is_current)->toBeFalse();
    });

    it('can delete a phase (soft delete)', function () {
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

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('delete')
            ->assertDispatched('phase-deleted');

        expect($phase->fresh()->trashed())->toBeTrue();
    });

    it('prevents deletion if phase has resolutions', function () {
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
        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('delete')
            ->assertDispatched('phase-delete-error');

        expect($phase->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted phase', function () {
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
        $phase->delete();

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('restore')
            ->assertDispatched('phase-restored');

        expect($phase->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a phase (only super-admin)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);
        $phase->delete();

        Livewire::test(Show::class, ['call' => $call, 'call_phase' => $phase])
            ->call('forceDelete')
            ->assertDispatched('phase-force-deleted');

        expect(CallPhase::withTrashed()->find($phase->id))->toBeNull();
    });
});
