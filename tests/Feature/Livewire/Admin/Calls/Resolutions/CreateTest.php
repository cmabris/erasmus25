<?php

use App\Livewire\Admin\Calls\Resolutions\Create;
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

describe('Admin Calls Resolutions Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.resolutions.create', $call))
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

        $this->get(route('admin.calls.resolutions.create', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.resolutions.create', $call))
            ->assertForbidden();
    });
});

describe('Admin Calls Resolutions Create - Successful Creation', function () {
    it('can create a resolution with valid data', function () {
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('description', 'Descripción de prueba')
            ->set('evaluation_procedure', 'Procedimiento de evaluación')
            ->set('official_date', '2024-01-01')
            ->set('published_at', '2024-01-15')
            ->call('save')
            ->assertDispatched('resolution-created')
            ->assertRedirect(route('admin.calls.resolutions.index', $call));

        $this->assertDatabaseHas('resolutions', [
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'provisional',
            'title' => 'Resolución Test',
            'description' => 'Descripción de prueba',
            'evaluation_procedure' => 'Procedimiento de evaluación',
            'created_by' => $user->id,
        ]);
    });

    it('sets created_by to authenticated user', function () {
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'definitivo')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('save');

        $resolution = Resolution::where('title', 'Resolución Test')->first();
        expect($resolution->created_by)->toBe($user->id);
    });

    it('can create resolution with PDF file', function () {
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

        // Create a temporary PDF file with actual content
        $tempFile = tempnam(sys_get_temp_dir(), 'test_resolution_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $pdf = UploadedFile::fake()->create('resolution.pdf', 100, 'application/pdf');
        // Override the fake file with real content
        file_put_contents($pdf->getRealPath(), '%PDF-1.4 Test PDF content');

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución con PDF')
            ->set('official_date', '2024-01-01')
            ->set('pdfFile', $pdf)
            ->call('save');

        $resolution = Resolution::where('title', 'Resolución con PDF')->first();
        expect($resolution)->not->toBeNull();
        expect($resolution->hasMedia('resolutions'))->toBeTrue();

        // Clean up
        @unlink($tempFile);
    });
});

describe('Admin Calls Resolutions Create - Validation', function () {
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->set('call_phase_id', null)
            ->call('save')
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', '')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('save')
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', '')
            ->set('official_date', '2024-01-01')
            ->call('save')
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', null)
            ->call('save')
            ->assertHasErrors(['official_date']);
    });

    it('validates type is in allowed values', function () {
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

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'invalid_type')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('save')
            ->assertHasErrors(['type']);
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

        $phase = CallPhase::factory()->create(['call_id' => $call2->id]);

        Livewire::test(Create::class, ['call' => $call1])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->call('save')
            ->assertHasErrors(['call_phase_id']);
    });

    it('validates PDF file is PDF type', function () {
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

        $file = UploadedFile::fake()->create('document.txt', 100, 'text/plain');

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->set('pdfFile', $file)
            ->call('save')
            ->assertHasErrors(['pdfFile']);
    });

    it('validates PDF file size is within limit', function () {
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

        // Create a file larger than 10MB (10240 KB)
        $pdf = UploadedFile::fake()->create('large.pdf', 11000, 'application/pdf');

        Livewire::test(Create::class, ['call' => $call])
            ->set('call_phase_id', $phase->id)
            ->set('type', 'provisional')
            ->set('title', 'Resolución Test')
            ->set('official_date', '2024-01-01')
            ->set('pdfFile', $pdf)
            ->call('save')
            ->assertHasErrors(['pdfFile']);
    });
});
