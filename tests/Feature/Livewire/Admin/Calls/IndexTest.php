<?php

use App\Livewire\Admin\Calls\Index;
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
    Permission::firstOrCreate(['name' => Permissions::CALLS_PUBLISH, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos incluyendo publish
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
        Permissions::CALLS_PUBLISH,
    ]);

    // Editor puede ver, crear y editar pero no publicar ni eliminar
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

describe('Admin Calls Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.calls.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with view permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.calls.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.calls.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Calls Index - Listing', function () {
    it('displays all calls by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria 1',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria 2',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Convocatoria 1')
            ->assertSee('Convocatoria 2');
    });

    it('displays call information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
            'type' => 'alumnado',
            'modality' => 'corta',
            'status' => 'abierta',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Convocatoria Test')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025');
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

        CallPhase::factory()->count(3)->create(['call_id' => $call->id]);
        Resolution::factory()->count(2)->create(['call_id' => $call->id]);

        Livewire::test(Index::class)
            ->assertSee('3') // phases count
            ->assertSee('2'); // resolutions count
    });

    it('hides deleted calls by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Activa',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Eliminada',
        ]);
        $call2->delete();

        Livewire::test(Index::class)
            ->assertSee('Convocatoria Activa')
            ->assertDontSee('Convocatoria Eliminada');
    });

    it('shows deleted calls when filter is enabled', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Eliminada',
        ]);
        $call->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->assertSee('Convocatoria Eliminada');
    });
});

describe('Admin Calls Index - Filtering', function () {
    it('filters calls by program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa 1']);
        $program2 = Program::factory()->create(['name' => 'Programa 2']);
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Call Programa 1',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program2->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Call Programa 2',
        ]);

        Livewire::test(Index::class)
            ->set('filterProgram', (string) $program1->id)
            ->assertSee('Call Programa 1')
            ->assertDontSee('Call Programa 2');
    });

    it('filters calls by academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $year1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year1->id,
            'title' => 'Call 2024-2025',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year2->id,
            'title' => 'Call 2025-2026',
        ]);

        Livewire::test(Index::class)
            ->set('filterAcademicYear', (string) $year1->id)
            ->assertSee('Call 2024-2025')
            ->assertDontSee('Call 2025-2026');
    });

    it('filters calls by type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
            'title' => 'Call Alumnado',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'personal',
            'title' => 'Call Personal',
        ]);

        Livewire::test(Index::class)
            ->set('filterType', 'alumnado')
            ->assertSee('Call Alumnado')
            ->assertDontSee('Call Personal');
    });

    it('filters calls by status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'abierta',
            'title' => 'Call Abierta',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'cerrada',
            'title' => 'Call Cerrada',
        ]);

        Livewire::test(Index::class)
            ->set('filterStatus', 'abierta')
            ->assertSee('Call Abierta')
            ->assertDontSee('Call Cerrada');
    });

    it('searches calls by title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Erasmus',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Movilidad',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Erasmus')
            ->assertSee('Convocatoria Erasmus')
            ->assertDontSee('Convocatoria Movilidad');
    });
});

describe('Admin Calls Index - Sorting', function () {
    it('sorts calls by created_at descending by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Call Antigua',
            'created_at' => now()->subDays(2),
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Call Nueva',
            'created_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSeeInOrder(['Call Nueva', 'Call Antigua']);
    });

    it('sorts calls by title ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Z Call',
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'A Call',
        ]);

        Livewire::test(Index::class)
            ->call('sortBy', 'title')
            ->assertSeeInOrder(['A Call', 'Z Call']);
    });
});

describe('Admin Calls Index - Actions', function () {
    it('can change call status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        Livewire::test(Index::class)
            ->call('changeStatus', $call->id, 'abierta')
            ->assertDispatched('call-updated');

        expect($call->fresh()->status)->toBe('abierta');
    });

    it('sets published_at when changing status to abierta', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        Livewire::test(Index::class)
            ->call('changeStatus', $call->id, 'abierta');

        expect($call->fresh()->published_at)->not->toBeNull();
    });

    it('can publish a call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        Livewire::test(Index::class)
            ->call('publish', $call->id)
            ->assertDispatched('call-published');

        $call->refresh();
        expect($call->status)->toBe('abierta');
        expect($call->published_at)->not->toBeNull();
    });

    it('can delete a call (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->set('callToDelete', $call->id)
            ->call('delete')
            ->assertDispatched('call-deleted');

        expect($call->fresh()->trashed())->toBeTrue();
    });

    it('prevents deletion if call has relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Index::class)
            ->set('callToDelete', $call->id)
            ->call('delete')
            ->assertDispatched('call-delete-error');

        expect($call->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->set('callToRestore', $call->id)
            ->call('restore')
            ->assertDispatched('call-restored');

        expect($call->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a call (only super-admin)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->set('callToForceDelete', $call->id)
            ->call('forceDelete')
            ->assertDispatched('call-force-deleted');

        expect(Call::withTrashed()->find($call->id))->toBeNull();
    });

    it('prevents force delete if call has relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call->delete();

        CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->set('callToForceDelete', $call->id)
            ->call('forceDelete')
            ->assertDispatched('call-force-delete-error');

        expect(Call::withTrashed()->find($call->id))->not->toBeNull();
    });
});
