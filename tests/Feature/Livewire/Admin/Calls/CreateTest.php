<?php

use App\Livewire\Admin\Calls\Create;
use App\Models\AcademicYear;
use App\Models\Call;
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

describe('Admin Calls Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.calls.create'))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.calls.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.calls.create'))
            ->assertForbidden();
    });
});

describe('Admin Calls Create - Successful Creation', function () {
    it('can create a call with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España', 'Francia'])
            ->set('status', 'borrador')
            ->call('store')
            ->assertRedirect(route('admin.calls.show', Call::where('title', 'Convocatoria Test')->first()));

        expect(Call::where('title', 'Convocatoria Test')->exists())->toBeTrue();
    });

    it('generates slug automatically from title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store');

        $call = Call::where('title', 'Convocatoria Test')->first();
        expect($call->slug)->toBe('convocatoria-test');
    });

    it('creates call with scoring table', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $scoringTable = [
            ['concept' => 'Expediente', 'max_points' => 40, 'description' => 'Nota media'],
            ['concept' => 'Idioma', 'max_points' => 30, 'description' => 'Nivel de idioma'],
        ];

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->set('scoringTable', $scoringTable)
            ->call('store');

        $call = Call::where('title', 'Convocatoria Test')->first();
        expect($call->scoring_table)->toBeArray();
        expect(count($call->scoring_table))->toBe(2);
    });

    it('sets created_by to current user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store');

        $call = Call::where('title', 'Convocatoria Test')->first();
        expect($call->created_by)->toBe($user->id);
    });
});

describe('Admin Calls Create - Validation', function () {
    it('requires program_id field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store')
            ->assertHasErrors(['program_id']);
    });

    it('requires academic_year_id field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store')
            ->assertHasErrors(['academic_year_id']);
    });

    it('requires title field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store')
            ->assertHasErrors(['title']);
    });

    it('requires at least one destination', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', [])
            ->call('store')
            ->assertHasErrors(['destinations']);
    });

    it('validates that estimated_end_date is after estimated_start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->set('estimated_start_date', '2025-06-01')
            ->set('estimated_end_date', '2025-05-01')
            ->call('store')
            ->assertHasErrors(['estimated_end_date']);
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Call::factory()->create(['slug' => 'convocatoria-test']);

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('slug', 'convocatoria-test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 10)
            ->set('destinations', ['España'])
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates number_of_places is at least 1', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('academic_year_id', $academicYear->id)
            ->set('title', 'Convocatoria Test')
            ->set('type', 'alumnado')
            ->set('modality', 'corta')
            ->set('number_of_places', 0)
            ->set('destinations', ['España'])
            ->call('store')
            ->assertHasErrors(['number_of_places']);
    });
});

describe('Admin Calls Create - Dynamic Fields', function () {
    it('can add destinations dynamically', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('newDestination', 'España')
            ->call('addDestination');

        // Verify that 'España' is in the destinations array
        $destinations = $component->get('destinations');
        expect($destinations)->toContain('España');
        expect($component->get('newDestination'))->toBe('');
    });

    it('can remove destinations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('destinations', ['España', 'Francia', 'Italia'])
            ->call('removeDestination', 1)
            ->assertSet('destinations', ['España', 'Italia']);
    });

    it('can add scoring items dynamically', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('newScoringItem.concept', 'Expediente')
            ->set('newScoringItem.max_points', 40)
            ->set('newScoringItem.description', 'Nota media')
            ->call('addScoringItem')
            ->assertCount('scoringTable', 2); // 1 inicial + 1 añadido
    });

    it('can remove scoring items', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('scoringTable', [
                ['concept' => 'Expediente', 'max_points' => 40, 'description' => 'Nota media'],
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => 'Nivel de idioma'],
            ])
            ->call('removeScoringItem', 0)
            ->assertCount('scoringTable', 1);
    });

    it('generates slug from title in real-time', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('title', 'Convocatoria Test')
            ->assertSet('slug', 'convocatoria-test');
    });
});
