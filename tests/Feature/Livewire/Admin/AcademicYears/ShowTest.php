<?php

use App\Livewire\Admin\AcademicYears\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
});

describe('Admin AcademicYears Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.show', $academicYear))
            ->assertRedirect('/login');
    });

    it('allows authenticated users to access (view returns true)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.show', $academicYear))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.show', $academicYear))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });
});

describe('Admin AcademicYears Show - Display', function () {
    it('displays academic year information', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => true,
        ]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->assertSee('2024-2025')
            ->assertSee('01/09/2024')
            ->assertSee('30/06/2025')
            ->assertSee('Año Actual');
    });

    it('displays statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->count(3)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        NewsPost::factory()->count(5)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $category = \App\Models\DocumentCategory::firstOrCreate(
            ['slug' => 'test-category-'.uniqid()],
            [
                'name' => 'Test Category '.uniqid(),
                'slug' => 'test-category-'.uniqid(),
                'order' => 1,
            ]
        );
        Document::factory()->count(2)->create([
            'category_id' => $category->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->assertSee('3')
            ->assertSee('5')
            ->assertSee('2');
    });

    it('displays related calls', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
        ]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->assertSee('Convocatoria Test');
    });

    it('displays related news posts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Test',
        ]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->assertSee('Noticia Test');
    });
});

describe('Admin AcademicYears Show - Toggle Current', function () {
    it('can toggle current status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['is_current' => false]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->call('toggleCurrent')
            ->assertDispatched('academic-year-updated');

        expect($academicYear->fresh()->is_current)->toBeTrue();
    });

    it('unmarks other years when marking one as current', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->current()->create(['year' => '2023-2024']);
        $year2 = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Show::class, ['academic_year' => $year2])
            ->call('toggleCurrent');

        expect($year1->fresh()->is_current)->toBeFalse();
        expect($year2->fresh()->is_current)->toBeTrue();
    });

    it('can unmark current status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->current()->create();

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->call('toggleCurrent')
            ->assertDispatched('academic-year-updated');

        expect($academicYear->fresh()->is_current)->toBeFalse();
    });
});

describe('Admin AcademicYears Show - Soft Delete', function () {
    it('can delete an academic year without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('academic-year-deleted')
            ->assertRedirect(route('admin.academic-years.index'));

        expect($academicYear->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete an academic year with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $program = Program::factory()->create();
        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('academic-year-delete-error');

        expect($academicYear->fresh()->trashed())->toBeFalse();
    });
});

describe('Admin AcademicYears Show - Restore', function () {
    it('can restore a deleted academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete();

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->set('showRestoreModal', true)
            ->call('restore')
            ->assertDispatched('academic-year-restored');

        expect($academicYear->fresh()->trashed())->toBeFalse();
    });
});

describe('Admin AcademicYears Show - Force Delete', function () {
    it('can force delete an academic year without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete();

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('academic-year-force-deleted')
            ->assertRedirect(route('admin.academic-years.index'));

        expect(AcademicYear::withTrashed()->find($academicYear->id))->toBeNull();
    });

    it('cannot force delete an academic year with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete(); // Soft delete first

        // Create relationships AFTER soft delete (they won't be auto-deleted)
        $program = Program::factory()->create();
        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Reload the academic year to get the trashed version
        $academicYear = AcademicYear::withTrashed()->find($academicYear->id);

        Livewire::test(Show::class, ['academic_year' => $academicYear])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('academic-year-force-delete-error');

        expect(AcademicYear::withTrashed()->find($academicYear->id))->not->toBeNull();
    });
});

describe('Admin AcademicYears Show - Computed Properties', function () {
    it('academicYearId computed property returns the correct ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $component = Livewire::test(Show::class, ['academic_year' => $academicYear]);

        // Call the method directly on the instance
        $academicYearId = $component->instance()->academicYearId();
        expect($academicYearId)->toBe($academicYear->id);
    });

    it('editUrl computed property returns the correct route', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $component = Livewire::test(Show::class, ['academic_year' => $academicYear]);

        // Call the method directly on the instance
        $editUrl = $component->instance()->editUrl();
        expect($editUrl)->toBe(route('admin.academic-years.edit', $academicYear->id));
    });
});
