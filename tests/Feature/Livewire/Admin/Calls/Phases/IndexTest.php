<?php

use App\Livewire\Admin\Calls\Phases\Index;
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

describe('Admin Calls Phases Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.phases.index', $call))
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

        $this->get(route('admin.calls.phases.index', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
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

        $this->get(route('admin.calls.phases.index', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Calls Phases Index - Listing', function () {
    it('displays all phases for a call by default', function () {
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
            'name' => 'Fase 1',
            'order' => 1,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase 2',
            'order' => 2,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Fase 1')
            ->assertSee('Fase 2');
    });

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
            'order' => 1,
            'is_current' => true,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Fase Test')
            ->assertSee('Publicación');
    });

    it('displays relationship counts', function () {
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

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('3'); // resolutions count
    });

    it('hides deleted phases by default', function () {
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
            'name' => 'Fase Activa',
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Eliminada',
        ]);
        $phase2->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Fase Activa')
            ->assertDontSee('Fase Eliminada');
    });

    it('shows deleted phases when filter is enabled', function () {
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
            'name' => 'Fase Eliminada',
        ]);
        $phase->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->assertSee('Fase Eliminada');
    });
});

describe('Admin Calls Phases Index - Filtering', function () {
    it('filters phases by phase type', function () {
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
            'phase_type' => 'publicacion',
            'name' => 'Fase Publicación',
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'phase_type' => 'solicitudes',
            'name' => 'Fase Solicitudes',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterPhaseType', 'publicacion')
            ->assertSee('Fase Publicación')
            ->assertDontSee('Fase Solicitudes');
    });

    it('filters phases by is_current status', function () {
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
            'is_current' => true,
            'name' => 'Fase Actual',
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
            'name' => 'Fase No Actual',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterIsCurrent', '1')
            ->assertSee('Fase Actual')
            ->assertDontSee('Fase No Actual');
    });

    it('searches phases by name', function () {
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
            'name' => 'Fase Publicación',
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Solicitudes',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('search', 'Publicación')
            ->assertSee('Fase Publicación')
            ->assertDontSee('Fase Solicitudes');
    });

    it('searches phases by description', function () {
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
            'name' => 'Fase 1',
            'description' => 'Descripción con palabra clave',
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase 2',
            'description' => 'Otra descripción',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('search', 'palabra clave')
            ->assertSee('Fase 1')
            ->assertDontSee('Fase 2');
    });
});

describe('Admin Calls Phases Index - Sorting', function () {
    it('sorts phases by order ascending by default', function () {
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
            'name' => 'Fase 2',
            'order' => 2,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase 1',
            'order' => 1,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->assertSeeInOrder(['Fase 1', 'Fase 2']);
    });

    it('sorts phases by name ascending', function () {
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
            'name' => 'Z Fase',
            'order' => 1,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'A Fase',
            'order' => 2,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('sortBy', 'name')
            ->assertSeeInOrder(['A Fase', 'Z Fase']);
    });
});

describe('Admin Calls Phases Index - Actions', function () {
    it('can mark a phase as current (unmarks others)', function () {
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
            'is_current' => true,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => false,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('markAsCurrent', $phase2->id)
            ->assertDispatched('phase-updated');

        expect($phase1->fresh()->is_current)->toBeFalse();
        expect($phase2->fresh()->is_current)->toBeTrue();
    });

    it('can unmark a phase as current', function () {
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

        Livewire::test(Index::class, ['call' => $call])
            ->call('unmarkAsCurrent', $phase->id)
            ->assertDispatched('phase-updated');

        expect($phase->fresh()->is_current)->toBeFalse();
    });

    it('can move phase up in order', function () {
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
            'order' => 1,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 2,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('moveUp', $phase2->id)
            ->assertDispatched('phase-reordered');

        expect($phase1->fresh()->order)->toBe(2);
        expect($phase2->fresh()->order)->toBe(1);
    });

    it('can move phase down in order', function () {
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
            'order' => 1,
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 2,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('moveDown', $phase1->id)
            ->assertDispatched('phase-reordered');

        expect($phase1->fresh()->order)->toBe(2);
        expect($phase2->fresh()->order)->toBe(1);
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

        Livewire::test(Index::class, ['call' => $call])
            ->call('confirmDelete', $phase->id)
            ->set('phaseToDelete', $phase->id)
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

        Livewire::test(Index::class, ['call' => $call])
            ->call('confirmDelete', $phase->id)
            ->set('phaseToDelete', $phase->id)
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

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('confirmRestore', $phase->id)
            ->set('phaseToRestore', $phase->id)
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

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $phase->id)
            ->set('phaseToForceDelete', $phase->id)
            ->call('forceDelete')
            ->assertDispatched('phase-force-deleted');

        expect(CallPhase::withTrashed()->find($phase->id))->toBeNull();
    });

    it('prevents force delete if phase has resolutions', function () {
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

        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $phase->id)
            ->set('phaseToForceDelete', $phase->id)
            ->call('forceDelete')
            ->assertDispatched('phase-force-delete-error');

        expect(CallPhase::withTrashed()->find($phase->id))->not->toBeNull();
    });
});
