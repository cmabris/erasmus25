<?php

use App\Livewire\Admin\Calls\Edit;
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

describe('Admin Calls Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.edit', $call))
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

        $this->get(route('admin.calls.edit', $call))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without update permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.calls.edit', $call))
            ->assertForbidden();
    });
});

describe('Admin Calls Edit - Successful Update', function () {
    it('can update a call with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Título Original',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', 'Título Actualizado')
            ->set('number_of_places', 20)
            ->call('update')
            ->assertRedirect(route('admin.calls.show', $call));

        expect($call->fresh()->title)->toBe('Título Actualizado');
        expect($call->fresh()->number_of_places)->toBe(20);
    });

    it('sets updated_by to current user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', 'Título Actualizado')
            ->call('update');

        expect($call->fresh()->updated_by)->toBe($user->id);
    });

    it('loads existing call data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Título Original',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España', 'Francia'],
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->assertSet('title', 'Título Original')
            ->assertSet('type', 'alumnado')
            ->assertSet('modality', 'corta')
            ->assertSet('number_of_places', 10)
            ->assertCount('destinations', 2);
    });

    it('converts old scoring_table format to new format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                'idioma' => 30,
                'entrevista' => 20,
            ],
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->assertCount('scoringTable', 2)
            ->assertSet('scoringTable.0.concept', 'idioma')
            ->assertSet('scoringTable.0.max_points', 30);
    });
});

describe('Admin Calls Edit - Validation', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', '')
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('validates slug uniqueness excluding current call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'convocatoria-1',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'convocatoria-2',
        ]);

        Livewire::test(Edit::class, ['call' => $call1])
            ->set('slug', 'convocatoria-2')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('allows same slug for the same call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'convocatoria-test',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('slug', 'convocatoria-test')
            ->call('update')
            ->assertHasNoErrors(['slug']);
    });
});

describe('Admin Calls Edit - Updated Hooks', function () {
    it('generates slug from title when slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => '',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('slug', '') // Ensure slug is empty
            ->set('title', 'Nueva Convocatoria Erasmus')
            ->assertSet('slug', 'nueva-convocatoria-erasmus');
    });

    it('validates title in real-time when changed', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', str_repeat('a', 300)) // Too long
            ->assertHasErrors(['title']);
    });

    it('validates slug uniqueness in real-time', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $existingCall = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'existing-slug',
        ]);
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'my-slug',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('slug', 'existing-slug')
            ->assertHasErrors(['slug']);
    });

    it('validates estimated start date before end date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('estimated_end_date', '2024-06-01')
            ->set('estimated_start_date', '2024-12-01') // After end date
            ->assertHasErrors(['estimated_start_date']);
    });

    it('validates estimated start date when no end date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('estimated_end_date', '')
            ->set('estimated_start_date', '2024-09-01')
            ->assertHasNoErrors(['estimated_start_date']);
    });

    it('validates estimated end date after start date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('estimated_start_date', '2024-12-01')
            ->set('estimated_end_date', '2024-01-01') // Before start date
            ->assertHasErrors(['estimated_end_date']);
    });

    it('validates estimated end date when no start date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('estimated_start_date', '')
            ->set('estimated_end_date', '2025-06-30')
            ->assertHasNoErrors(['estimated_end_date']);
    });

    it('validates program_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('program_id', 99999) // Non-existent program
            ->assertHasErrors(['program_id']);
    });

    it('validates academic_year_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('academic_year_id', 99999) // Non-existent academic year
            ->assertHasErrors(['academic_year_id']);
    });

    it('validates number_of_places is at least 1', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('number_of_places', 0)
            ->assertHasErrors(['number_of_places']);
    });
});

describe('Admin Calls Edit - Destinations Management', function () {
    it('can add a new destination with value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia'],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->set('newDestination', 'Alemania')
            ->call('addDestination');

        expect($component->get('destinations'))->toContain('Alemania')
            ->and($component->get('newDestination'))->toBe('');
    });

    it('can add an empty destination', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia'],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->set('newDestination', '')
            ->call('addDestination');

        expect(count($component->get('destinations')))->toBe(2);
    });

    it('can remove a destination when more than one exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia', 'Alemania', 'Italia'],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('removeDestination', 1); // Remove 'Alemania'

        expect($component->get('destinations'))->not->toContain('Alemania')
            ->and(count($component->get('destinations')))->toBe(2);
    });

    it('cannot remove last destination', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia'],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('removeDestination', 0);

        // Should still have one destination
        expect(count($component->get('destinations')))->toBe(1);
    });

    it('can update a destination value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia', 'Alemania'],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateDestination', 0, 'España');

        expect($component->get('destinations.0'))->toBe('España');
    });

    it('handles updating non-existent destination index gracefully', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia'],
        ]);

        // Should not throw error
        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateDestination', 99, 'España');

        expect($component->get('destinations'))->toBe(['Francia']);
    });
});

describe('Admin Calls Edit - Scoring Table Management', function () {
    it('can add a scoring item', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->set('newScoringItem.concept', 'Idioma')
            ->set('newScoringItem.max_points', 30)
            ->set('newScoringItem.description', 'Nivel de idioma')
            ->call('addScoringItem');

        $scoringTable = $component->get('scoringTable');
        $lastItem = end($scoringTable);

        expect($lastItem['concept'])->toBe('Idioma')
            ->and($lastItem['max_points'])->toBe(30)
            ->and($lastItem['description'])->toBe('Nivel de idioma');

        // New scoring item should be reset
        expect($component->get('newScoringItem.concept'))->toBe('');
    });

    it('can remove a scoring item when more than one exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
                ['concept' => 'Expediente', 'max_points' => 40, 'description' => ''],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('removeScoringItem', 0);

        expect(count($component->get('scoringTable')))->toBe(1)
            ->and($component->get('scoringTable.0.concept'))->toBe('Expediente');
    });

    it('cannot remove last scoring item', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('removeScoringItem', 0);

        // Should still have one item
        expect(count($component->get('scoringTable')))->toBe(1);
    });

    it('can update scoring item concept', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateScoringItem', 0, 'concept', 'Nivel de Inglés');

        expect($component->get('scoringTable.0.concept'))->toBe('Nivel de Inglés');
    });

    it('can update scoring item max_points as integer', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateScoringItem', 0, 'max_points', '50');

        expect($component->get('scoringTable.0.max_points'))->toBe(50)
            ->and($component->get('scoringTable.0.max_points'))->toBeInt();
    });

    it('can update scoring item description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateScoringItem', 0, 'description', 'Nivel B2 o superior');

        expect($component->get('scoringTable.0.description'))->toBe('Nivel B2 o superior');
    });

    it('handles updating non-existent scoring item index gracefully', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
            ],
        ]);

        // Should not throw error
        $component = Livewire::test(Edit::class, ['call' => $call])
            ->call('updateScoringItem', 99, 'concept', 'Test');

        expect($component->get('scoringTable.0.concept'))->toBe('Idioma');
    });
});

describe('Admin Calls Edit - Mount Scoring Table Normalization', function () {
    it('initializes empty scoring table with one empty item', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => null,
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call]);

        expect(count($component->get('scoringTable')))->toBe(1)
            ->and($component->get('scoringTable.0.concept'))->toBe('')
            ->and($component->get('scoringTable.0.max_points'))->toBe(0);
    });

    it('preserves new format scoring table', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => 'Nivel B2'],
                ['concept' => 'Expediente', 'max_points' => 40, 'description' => 'Nota media'],
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call]);

        expect(count($component->get('scoringTable')))->toBe(2)
            ->and($component->get('scoringTable.0.concept'))->toBe('Idioma')
            ->and($component->get('scoringTable.0.description'))->toBe('Nivel B2')
            ->and($component->get('scoringTable.1.concept'))->toBe('Expediente');
    });

    it('converts old format scoring table with string keys', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        // Old format: associative array with concept as key and points as value
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                'Idioma' => 30,
                'Expediente' => 40,
                'Entrevista' => 30,
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call]);

        expect(count($component->get('scoringTable')))->toBe(3)
            ->and($component->get('scoringTable.0.concept'))->toBe('Idioma')
            ->and($component->get('scoringTable.0.max_points'))->toBe(30)
            ->and($component->get('scoringTable.0.description'))->toBe('')
            ->and($component->get('scoringTable.1.concept'))->toBe('Expediente')
            ->and($component->get('scoringTable.1.max_points'))->toBe(40);
    });

    it('handles old format with non-numeric points', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        // Old format with non-numeric value
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'scoring_table' => [
                'Idioma' => 'treinta', // Non-numeric
            ],
        ]);

        $component = Livewire::test(Edit::class, ['call' => $call]);

        expect($component->get('scoringTable.0.concept'))->toBe('Idioma')
            ->and($component->get('scoringTable.0.max_points'))->toBe(0); // Should default to 0
    });
});

describe('Admin Calls Edit - Update with Different Scenarios', function () {
    it('filters empty destinations before saving', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['Francia'],
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('destinations', ['Francia', '', 'Alemania', '  '])
            ->call('update');

        $updatedCall = $call->fresh();
        expect($updatedCall->destinations)->toBe(['Francia', 'Alemania']);
    });

    it('filters empty scoring items before saving', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('scoringTable', [
                ['concept' => 'Idioma', 'max_points' => 30, 'description' => ''],
                ['concept' => '', 'max_points' => 0, 'description' => ''], // Empty
                ['concept' => 'Expediente', 'max_points' => 40, 'description' => 'Nota media'],
            ])
            ->call('update');

        $updatedCall = $call->fresh();
        expect(count($updatedCall->scoring_table))->toBe(2);
    });

    it('generates slug if empty when updating', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Título Original',
            'slug' => 'titulo-original',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', 'Nuevo Título para Convocatoria')
            ->set('slug', '')
            ->call('update');

        $updatedCall = $call->fresh();
        expect($updatedCall->slug)->toBe('nuevo-titulo-para-convocatoria');
    });

    it('dispatches call-updated event after successful update', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('title', 'Título Actualizado')
            ->call('update')
            ->assertDispatched('call-updated');
    });

    it('can update status field', function () {
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

        Livewire::test(Edit::class, ['call' => $call])
            ->set('status', 'abierta')
            ->call('update');

        expect($call->fresh()->status)->toBe('abierta');
    });

    it('can update type and modality', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
            'modality' => 'corta',
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('type', 'personal')
            ->set('modality', 'larga')
            ->call('update');

        $updatedCall = $call->fresh();
        expect($updatedCall->type)->toBe('personal')
            ->and($updatedCall->modality)->toBe('larga');
    });

    it('validates invalid type value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('type', 'invalid_type')
            ->call('update')
            ->assertHasErrors(['type']);
    });

    it('validates invalid modality value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['call' => $call])
            ->set('modality', 'invalid_modality')
            ->call('update')
            ->assertHasErrors(['modality']);
    });
});
