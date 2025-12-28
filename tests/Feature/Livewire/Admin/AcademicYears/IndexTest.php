<?php

use App\Livewire\Admin\AcademicYears\Index;
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

describe('Admin AcademicYears Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.academic-years.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users to access (viewAny returns true)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin AcademicYears Index - Listing', function () {
    it('displays all academic years by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        Livewire::test(Index::class)
            ->assertSee('2024-2025')
            ->assertSee('2025-2026');
    });

    it('displays academic year information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee('2024-2025')
            ->assertSee('01/09/2024')
            ->assertSee('30/06/2025')
            ->assertSee('Año Actual');
    });

    it('displays relationship counts', function () {
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
            ['slug' => 'test-category-' . uniqid()],
            [
                'name' => 'Test Category ' . uniqid(),
                'slug' => 'test-category-' . uniqid(),
                'order' => 1,
            ]
        );
        Document::factory()->count(2)->create([
            'category_id' => $category->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('3')
            ->assertSee('5')
            ->assertSee('2');
    });
});

describe('Admin AcademicYears Index - Search', function () {
    it('can search academic years by year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        $component = Livewire::test(Index::class)
            ->set('search', '2024-2025'); // Use exact format to avoid partial matches

        $years = $component->get('academicYears');
        $yearValues = $years->pluck('year')->toArray();
        expect($yearValues)->toContain('2024-2025')
            ->and($yearValues)->not->toContain('2025-2026');
    });

    it('can search academic years by exact year format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        AcademicYear::factory()->create(['year' => '2024-2025']);
        AcademicYear::factory()->create(['year' => '2025-2026']);

        Livewire::test(Index::class)
            ->set('search', '2024-2025')
            ->assertSee('2024-2025')
            ->assertDontSee('2025-2026');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Create years with unique years to avoid collisions
        for ($i = 0; $i < 20; $i++) {
            $startYear = 2020 + $i;
            $endYear = $startYear + 1;
            AcademicYear::factory()->create(['year' => "{$startYear}-{$endYear}"]);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', '2024');

        expect($component->get('search'))->toBe('2024');
        // Verify that search was applied and pagination reset
        expect($component->get('academicYears')->currentPage())->toBe(1);
    });
});

describe('Admin AcademicYears Index - Sorting', function () {
    it('can sort by year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        AcademicYear::factory()->create(['year' => '2025-2026']);
        AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Index::class)
            ->call('sortBy', 'year')
            ->assertSeeInOrder(['2024-2025', '2025-2026']);
    });

    it('can sort by start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->create(['year' => '2024-2025', 'start_date' => '2024-09-01']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026', 'start_date' => '2025-09-01']);

        Livewire::test(Index::class)
            ->call('sortBy', 'start_date')
            ->assertSee('2024-2025')
            ->assertSee('2025-2026');
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        AcademicYear::factory()->create(['year' => '2024-2025']);
        AcademicYear::factory()->create(['year' => '2025-2026']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'year');

        expect($component->get('sortDirection'))->toBe('asc');

        $component->call('sortBy', 'year');

        expect($component->get('sortDirection'))->toBe('desc');
    });
});

describe('Admin AcademicYears Index - Filters', function () {
    it('shows only non-deleted academic years by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $deletedYear = AcademicYear::factory()->create(['year' => '2023-2024']);
        $deletedYear->delete();

        Livewire::test(Index::class)
            ->assertSee('2024-2025')
            ->assertDontSee('2023-2024');
    });

    it('can show deleted academic years', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $deletedYear = AcademicYear::factory()->create(['year' => '2023-2024']);
        $deletedYear->delete();

        $component = Livewire::test(Index::class)
            ->set('showDeleted', '1');

        // When showDeleted is '1', only trashed records are shown
        $years = $component->get('academicYears');
        $yearValues = $years->pluck('year')->toArray();
        expect($yearValues)->toContain('2023-2024')
            ->and($yearValues)->not->toContain('2024-2025');

        // Verify deleted year is actually trashed
        $deletedYearFromResults = $years->firstWhere('year', '2023-2024');
        expect($deletedYearFromResults->trashed())->toBeTrue();
    });
});

describe('Admin AcademicYears Index - Pagination', function () {
    it('paginates academic years', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Create years with unique years to avoid collisions
        for ($i = 0; $i < 20; $i++) {
            $startYear = 2020 + $i;
            $endYear = $startYear + 1;
            AcademicYear::factory()->create(['year' => "{$startYear}-{$endYear}"]);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        // Verify pagination exists
        expect($component->get('academicYears')->hasPages())->toBeTrue();
        expect($component->get('academicYears')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Create years with unique years to avoid collisions
        for ($i = 0; $i < 20; $i++) {
            $startYear = 2020 + $i;
            $endYear = $startYear + 1;
            AcademicYear::factory()->create(['year' => "{$startYear}-{$endYear}"]);
        }

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        expect($component->get('academicYears')->count())->toBe(20); // All 20 should fit in one page
    });
});

describe('Admin AcademicYears Index - Toggle Current', function () {
    it('can toggle current status from index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->current()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        Livewire::test(Index::class)
            ->call('toggleCurrent', $year2->id);

        expect($year1->fresh()->is_current)->toBeFalse();
        expect($year2->fresh()->is_current)->toBeTrue();
    });

    it('unmarks other years when marking one as current', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->current()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        Livewire::test(Index::class)
            ->call('toggleCurrent', $year2->id);

        expect($year1->fresh()->is_current)->toBeFalse();
        expect($year2->fresh()->is_current)->toBeTrue();
    });
});

describe('Admin AcademicYears Index - Soft Delete', function () {
    it('can delete an academic year without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $academicYear->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('academicYearToDelete', $academicYear->id)
            ->call('delete')
            ->assertDispatched('academic-year-deleted');

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

        Livewire::test(Index::class)
            ->call('confirmDelete', $academicYear->id)
            ->call('delete')
            ->assertDispatched('academic-year-delete-error');

        expect($academicYear->fresh()->trashed())->toBeFalse();
    });
});

describe('Admin AcademicYears Index - Restore', function () {
    it('can restore a deleted academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $academicYear->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('academicYearToRestore', $academicYear->id)
            ->call('restore')
            ->assertDispatched('academic-year-restored');

        expect($academicYear->fresh()->trashed())->toBeFalse();
    });
});

describe('Admin AcademicYears Index - Force Delete', function () {
    it('can force delete an academic year without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $academicYear->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('academicYearToForceDelete', $academicYear->id)
            ->call('forceDelete')
            ->assertDispatched('academic-year-force-deleted');

        expect(AcademicYear::withTrashed()->find($academicYear->id))->toBeNull();
    });

    it('cannot force delete an academic year with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear->delete(); // Soft delete first

        // Create relationships AFTER soft delete (they won't be auto-deleted)
        $program = Program::factory()->create();
        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $academicYear->id)
            ->call('forceDelete')
            ->assertDispatched('academic-year-force-delete-error');

        expect(AcademicYear::withTrashed()->find($academicYear->id))->not->toBeNull();
    });
});
