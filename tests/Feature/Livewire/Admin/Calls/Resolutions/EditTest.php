<?php

use App\Livewire\Admin\Calls\Resolutions\Edit;
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

describe('Admin Calls Resolutions Edit - Authorization', function () {
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

        $this->get(route('admin.calls.resolutions.edit', [$call, $resolution]))
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
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        $this->get(route('admin.calls.resolutions.edit', [$call, $resolution]))
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
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        $this->get(route('admin.calls.resolutions.edit', [$call, $resolution]))
            ->assertForbidden();
    });
});

describe('Admin Calls Resolutions Edit - Successful Update', function () {
    it('can update a resolution with valid data', function () {
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
            'title' => 'Resolución Original',
            'type' => 'provisional',
        ]);

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('title', 'Resolución Actualizada')
            ->set('description', 'Nueva descripción')
            ->set('type', 'definitivo')
            ->set('official_date', '2024-02-01')
            ->set('published_at', '2024-02-15')
            ->call('update')
            ->assertDispatched('resolution-updated')
            ->assertRedirect(route('admin.calls.resolutions.show', [$call, $resolution]));

        $this->assertDatabaseHas('resolutions', [
            'id' => $resolution->id,
            'title' => 'Resolución Actualizada',
            'description' => 'Nueva descripción',
            'type' => 'definitivo',
        ]);
    });

    it('can update resolution with new PDF file', function () {
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

        // Create a temporary PDF file with actual content
        $pdf = UploadedFile::fake()->create('new-resolution.pdf', 100, 'application/pdf');
        // Override the fake file with real content
        file_put_contents($pdf->getRealPath(), '%PDF-1.4 Test PDF content');

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('title', $resolution->title)
            ->set('official_date', $resolution->official_date->format('Y-m-d'))
            ->set('pdfFile', $pdf)
            ->call('update');

        $resolution->refresh();
        expect($resolution->hasMedia('resolutions'))->toBeTrue();

        // Clean up
        @unlink($tempFile);
    });

    it('can remove existing PDF', function () {
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

        // Add existing PDF using temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resolution_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $resolution->addMedia($tempFile)
            ->usingName($resolution->title)
            ->usingFileName('existing.pdf')
            ->toMediaCollection('resolutions');

        expect($resolution->hasMedia('resolutions'))->toBeTrue();

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('title', $resolution->title)
            ->set('official_date', $resolution->official_date->format('Y-m-d'))
            ->set('removeExistingPdf', true)
            ->call('update');

        $resolution->refresh();
        expect($resolution->hasMedia('resolutions'))->toBeFalse();

        // Clean up
        @unlink($tempFile);
    });
});

describe('Admin Calls Resolutions Edit - Validation', function () {
    it('requires call_phase_id', function () {
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

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('call_phase_id', null)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('update')
            ->assertHasErrors(['call_phase_id']);
    });

    it('requires type', function () {
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

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('type', '')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('update')
            ->assertHasErrors(['type']);
    });

    it('requires title', function () {
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

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('type', 'provisional')
            ->set('title', '')
            ->set('official_date', '2024-01-01')
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('requires official_date', function () {
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

        Livewire::test(Edit::class, ['call' => $call, 'resolution' => $resolution])
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', null)
            ->call('update')
            ->assertHasErrors(['official_date']);
    });

    it('validates call_phase_id belongs to call_id', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $phase1 = CallPhase::factory()->create(['call_id' => $call1->id]);
        $phase2 = CallPhase::factory()->create(['call_id' => $call2->id]);

        $resolution = Resolution::factory()->create([
            'call_id' => $call1->id,
            'call_phase_id' => $phase1->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call1, 'resolution' => $resolution])
            ->set('call_phase_id', $phase2->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('update')
            ->assertHasErrors(['call_phase_id']);
    });
});
