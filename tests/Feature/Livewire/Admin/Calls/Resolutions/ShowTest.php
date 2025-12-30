<?php

use App\Livewire\Admin\Calls\Resolutions\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

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

describe('Admin Calls Resolutions Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
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

        $this->get(route('admin.calls.resolutions.show', [$call, $resolution]))
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
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        $this->get(route('admin.calls.resolutions.show', [$call, $resolution]))
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
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        $this->get(route('admin.calls.resolutions.show', [$call, $resolution]))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });
});

describe('Admin Calls Resolutions Show - Display', function () {
    it('displays resolution details correctly', function () {
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
            'description' => 'Descripción de prueba',
            'evaluation_procedure' => 'Procedimiento de evaluación',
        ]);

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->assertSee('Resolución Test')
            ->assertSee('Provisional')
            ->assertSee('Descripción de prueba')
            ->assertSee('Procedimiento de evaluación');
    });

    it('displays PDF if resolution has one', function () {
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

        // Add PDF using temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resolution_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $resolution->addMedia($tempFile)
            ->usingName($resolution->title)
            ->usingFileName('resolution.pdf')
            ->toMediaCollection('resolutions');

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->assertSee('resolution.pdf');

        // Clean up
        @unlink($tempFile);
    });
});

describe('Admin Calls Resolutions Show - Actions', function () {
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

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->call('publish')
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

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->call('unpublish')
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

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->call('delete')
            ->assertDispatched('resolution-deleted')
            ->assertRedirect(route('admin.calls.resolutions.index', $call));

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

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
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

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->call('forceDelete')
            ->assertDispatched('resolution-force-deleted')
            ->assertRedirect(route('admin.calls.resolutions.index', $call));

        expect(Resolution::withTrashed()->find($resolution->id))->toBeNull();
    });

    it('allows admin to force delete (policy allows CALLS_DELETE)', function () {
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

        // Reload the resolution to get trashed state
        $resolution = Resolution::withTrashed()->find($resolution->id);

        Livewire::test(Show::class, ['call' => $call, 'resolution' => $resolution])
            ->call('forceDelete')
            ->assertDispatched('resolution-force-deleted')
            ->assertRedirect(route('admin.calls.resolutions.index', $call));

        expect(Resolution::withTrashed()->find($resolution->id))->toBeNull();
    });
});
