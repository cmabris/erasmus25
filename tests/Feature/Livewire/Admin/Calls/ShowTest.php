<?php

use App\Livewire\Admin\Calls\Show;
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

describe('Admin Calls Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.show', $call))
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

        $this->get(route('admin.calls.show', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });
});

describe('Admin Calls Show - Display', function () {
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
            'number_of_places' => 10,
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->assertSee('Convocatoria Test')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025')
            ->assertSee('10');
    });

    it('displays statistics correctly', function () {
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

        Livewire::test(Show::class, ['call' => $call])
            ->assertSee('3') // phases
            ->assertSee('2'); // resolutions
    });

    it('displays phases correctly', function () {
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
            'phase_type' => 'publicacion',
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->assertSee('Fase Test');
    });

    it('displays resolutions correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'title' => 'Resolución Test',
            'type' => 'provisional',
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->assertSee('Resolución Test');
    });
});

describe('Admin Calls Show - Actions', function () {
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('changeStatus', 'abierta')
            ->assertDispatched('call-updated');

        expect($call->fresh()->status)->toBe('abierta');
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('publish')
            ->assertDispatched('call-published');

        $call->refresh();
        expect($call->status)->toBe('abierta');
        expect($call->published_at)->not->toBeNull();
    });

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

        Livewire::test(Show::class, ['call' => $call])
            ->call('markPhaseAsCurrent', $phase->id)
            ->assertDispatched('phase-updated');

        expect($phase->fresh()->is_current)->toBeTrue();
    });

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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'published_at' => null,
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->call('publishResolution', $resolution->id)
            ->assertDispatched('resolution-published');

        expect($resolution->fresh()->published_at)->not->toBeNull();
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('delete')
            ->assertDispatched('call-deleted')
            ->assertRedirect(route('admin.calls.index'));

        expect($call->fresh()->trashed())->toBeTrue();
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('restore')
            ->assertDispatched('call-restored');

        expect($call->fresh()->trashed())->toBeFalse();
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('unmarkPhaseAsCurrent', $phase->id)
            ->assertDispatched('phase-updated');

        expect($phase->fresh()->is_current)->toBeFalse();
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

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'published_at' => now(),
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->call('unpublishResolution', $resolution->id)
            ->assertDispatched('resolution-unpublished');

        expect($resolution->fresh()->published_at)->toBeNull();
    });

    it('sets closed_at when changing status to cerrada', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'abierta',
            'closed_at' => null,
        ]);

        Livewire::test(Show::class, ['call' => $call])
            ->call('changeStatus', 'cerrada')
            ->assertDispatched('call-updated');

        expect($call->fresh()->status)->toBe('cerrada')
            ->and($call->fresh()->closed_at)->not->toBeNull();
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

        Livewire::test(Show::class, ['call' => $call])
            ->call('changeStatus', 'abierta')
            ->assertDispatched('call-updated');

        expect($call->fresh()->status)->toBe('abierta')
            ->and($call->fresh()->published_at)->not->toBeNull();
    });

    it('cannot delete a call with phases', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Add a phase to the call
        CallPhase::factory()->create(['call_id' => $call->id]);

        Livewire::test(Show::class, ['call' => $call])
            ->call('delete')
            ->assertDispatched('call-delete-error');

        expect($call->fresh()->trashed())->toBeFalse();
    });

    it('cannot delete a call with resolutions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Add a resolution to the call
        Resolution::factory()->create(['call_id' => $call->id]);

        Livewire::test(Show::class, ['call' => $call])
            ->call('delete')
            ->assertDispatched('call-delete-error');

        expect($call->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a call without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call->delete(); // Soft delete first
        $callId = $call->id;

        Livewire::test(Show::class, ['call' => $call])
            ->call('forceDelete')
            ->assertDispatched('call-force-deleted')
            ->assertRedirect(route('admin.calls.index'));

        expect(Call::withTrashed()->find($callId))->toBeNull();
    });

    // Note: Cannot test "cannot force delete with phases" because phases are cascade-deleted
    // when the Call is soft-deleted (foreign key with cascadeOnDelete). The scenario of
    // a soft-deleted Call with existing phases is not possible in this application.
});

describe('Admin Calls Show - Helper Methods', function () {
    it('returns correct status color', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->getStatusColor('borrador'))->toBe('gray')
            ->and($component->instance()->getStatusColor('abierta'))->toBe('green')
            ->and($component->instance()->getStatusColor('cerrada'))->toBe('yellow')
            ->and($component->instance()->getStatusColor('en_baremacion'))->toBe('blue')
            ->and($component->instance()->getStatusColor('resuelta'))->toBe('purple')
            ->and($component->instance()->getStatusColor('archivada'))->toBe('zinc')
            ->and($component->instance()->getStatusColor('unknown'))->toBe('gray');
    });

    it('returns valid status transitions for borrador', function () {
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

        $component = Livewire::test(Show::class, ['call' => $call]);

        $transitions = $component->instance()->getValidStatusTransitions();
        expect($transitions)->toContain('abierta')
            ->and($transitions)->toContain('en_baremacion');
    });

    it('returns valid status transitions for abierta', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'abierta',
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        $transitions = $component->instance()->getValidStatusTransitions();
        expect($transitions)->toContain('cerrada')
            ->and($transitions)->toContain('en_baremacion');
    });

    it('returns empty transitions for archivada', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'archivada',
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        $transitions = $component->instance()->getValidStatusTransitions();
        expect($transitions)->toBeEmpty();
    });

    it('returns correct status description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->getStatusDescription('borrador'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('abierta'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('cerrada'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('en_baremacion'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('resuelta'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('archivada'))->not->toBeEmpty()
            ->and($component->instance()->getStatusDescription('unknown'))->toBe('');
    });

    it('returns canDelete false when call has relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Add a phase
        CallPhase::factory()->create(['call_id' => $call->id]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->canDelete())->toBeFalse();
    });

    it('returns canDelete true when call has no relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->canDelete())->toBeTrue();
    });

    it('returns canDelete false when user lacks permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER); // No delete permission
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->canDelete())->toBeFalse();
    });

    it('returns hasRelationships true when call has phases', function () {
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

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->hasRelationships)->toBeTrue();
    });

    it('returns hasRelationships true when call has resolutions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Resolution::factory()->create(['call_id' => $call->id]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->hasRelationships)->toBeTrue();
    });

    it('returns hasRelationships false when call has no relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['call' => $call]);

        expect($component->instance()->hasRelationships)->toBeFalse();
    });
});
