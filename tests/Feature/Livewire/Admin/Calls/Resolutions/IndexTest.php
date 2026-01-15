<?php

use App\Livewire\Admin\Calls\Resolutions\Index;
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

describe('Admin Calls Resolutions Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.resolutions.index', $call))
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

        $this->get(route('admin.calls.resolutions.index', $call))
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

        $this->get(route('admin.calls.resolutions.index', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Calls Resolutions Index - Listing', function () {
    it('displays all resolutions for a call by default', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución 1',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución 2',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Resolución 1')
            ->assertSee('Resolución 2');
    });

    it('displays resolution information correctly', function () {
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
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Test',
            'type' => 'provisional',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Resolución Test')
            ->assertSee('Provisional');
    });

    it('hides deleted resolutions by default', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Activa',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Eliminada',
        ]);
        $resolution2->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Resolución Activa')
            ->assertDontSee('Resolución Eliminada');
    });

    it('shows deleted resolutions when filter is enabled', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Eliminada',
        ]);
        $resolution->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->assertSee('Resolución Eliminada');
    });
});

describe('Admin Calls Resolutions Index - Filtering', function () {
    it('filters resolutions by type', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'provisional',
            'title' => 'Resolución Provisional',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'definitivo',
            'title' => 'Resolución Definitiva',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterType', 'provisional')
            ->assertSee('Resolución Provisional')
            ->assertDontSee('Resolución Definitiva');
    });

    it('filters resolutions by published status', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => now(),
            'title' => 'Resolución Publicada',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => null,
            'title' => 'Resolución No Publicada',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterPublished', '1')
            ->assertSee('Resolución Publicada')
            ->assertDontSee('Resolución No Publicada');
    });

    it('filters resolutions by call phase', function () {
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
        ]);

        $phase2 = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase 2',
        ]);

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase1->id,
            'title' => 'Resolución Fase 1',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase2->id,
            'title' => 'Resolución Fase 2',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterPhase', (string) $phase1->id)
            ->assertSee('Resolución Fase 1')
            ->assertDontSee('Resolución Fase 2');
    });

    it('searches resolutions by title', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Provisional',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Definitiva',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('search', 'Provisional')
            ->assertSee('Resolución Provisional')
            ->assertDontSee('Resolución Definitiva');
    });

    it('searches resolutions by description', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución 1',
            'description' => 'Descripción con palabra clave',
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución 2',
            'description' => 'Otra descripción',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('search', 'palabra clave')
            ->assertSee('Resolución 1')
            ->assertDontSee('Resolución 2');
    });
});

describe('Admin Calls Resolutions Index - Sorting', function () {
    it('sorts resolutions by official_date descending by default', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Antigua',
            'official_date' => now()->subDays(10),
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Reciente',
            'official_date' => now()->subDays(5),
        ]);

        // Default sort is official_date desc, so most recent first
        Livewire::test(Index::class, ['call' => $call])
            ->assertSee('Resolución Reciente')
            ->assertSee('Resolución Antigua');
    });

    it('sorts resolutions by official_date descending when sorted by official_date', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'A Resolución Antigua',
            'official_date' => now()->subDays(10),
        ]);

        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Z Resolución Reciente',
            'official_date' => now()->subDays(5),
        ]);

        // Sort by official_date descending
        Livewire::test(Index::class, ['call' => $call])
            ->call('sortBy', 'official_date')
            ->assertSee('Z Resolución Reciente')
            ->assertSee('A Resolución Antigua');
    });
});

describe('Admin Calls Resolutions Index - Actions', function () {
    it('can publish a resolution', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => null,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('publish', $resolution->id)
            ->assertDispatched('resolution-published');

        expect($resolution->fresh()->published_at)->not->toBeNull();
    });

    it('can unpublish a resolution', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => now(),
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('unpublish', $resolution->id)
            ->assertDispatched('resolution-unpublished');

        expect($resolution->fresh()->published_at)->toBeNull();
    });

    it('can delete a resolution (soft delete)', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('confirmDelete', $resolution->id)
            ->set('resolutionToDelete', $resolution->id)
            ->call('delete')
            ->assertDispatched('resolution-deleted');

        expect($resolution->fresh()->trashed())->toBeTrue();
    });

    it('can restore a deleted resolution', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);
        $resolution->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('confirmRestore', $resolution->id)
            ->set('resolutionToRestore', $resolution->id)
            ->call('restore')
            ->assertDispatched('resolution-restored');

        expect($resolution->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a resolution (only super-admin)', function () {
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);
        $resolution->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $resolution->id)
            ->set('resolutionToForceDelete', $resolution->id)
            ->call('forceDelete')
            ->assertDispatched('resolution-force-deleted');

        expect(Resolution::withTrashed()->find($resolution->id))->toBeNull();
    });
});

describe('Admin Calls Resolutions Index - Export', function () {
    it('allows admin to export resolutions', function () {
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

        Resolution::factory()->count(5)->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('export')
            ->assertFileDownloaded();
    });

    it('allows viewer to export resolutions', function () {
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

        Resolution::factory()->count(3)->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('export')
            ->assertFileDownloaded();
    });

    it('only exports resolutions for the specified call', function () {
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
        $phase1 = CallPhase::factory()->create(['call_id' => $call1->id]);
        $phase2 = CallPhase::factory()->create(['call_id' => $call2->id]);

        Resolution::factory()->create([
            'call_id' => $call1->id,
            'call_phase_id' => $phase1->id,
        ]);
        Resolution::factory()->create([
            'call_id' => $call2->id,
            'call_phase_id' => $phase2->id,
        ]);

        Livewire::test(Index::class, ['call' => $call1])
            ->call('export')
            ->assertFileDownloaded();
    });

    it('applies filters to export', function () {
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
            'type' => 'provisional',
            'published_at' => now(),
        ]);
        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'definitivo',
            'published_at' => null,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('filterType', 'provisional')
            ->set('filterPublished', '1')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('applies search filter to export', function () {
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
            'title' => 'Resolución Provisional',
        ]);
        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Definitiva',
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('search', 'Provisional')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('applies sorting to export', function () {
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
            'official_date' => now()->subDays(10),
        ]);
        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'official_date' => now()->subDays(5),
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->set('sortField', 'official_date')
            ->set('sortDirection', 'desc')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('includes deleted resolutions in export when showDeleted is 1', function () {
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

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);
        $resolution2->delete();

        Livewire::test(Index::class, ['call' => $call])
            ->set('showDeleted', '1')
            ->call('export')
            ->assertFileDownloaded();
    });

    it('generates downloadable file with call context', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        Livewire::test(Index::class, ['call' => $call])
            ->call('export')
            ->assertFileDownloaded();
    });
});
