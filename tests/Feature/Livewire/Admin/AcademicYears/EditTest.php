<?php

use App\Livewire\Admin\AcademicYears\Edit;
use App\Models\AcademicYear;
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

describe('Admin AcademicYears Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.edit', $academicYear))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.edit', $academicYear))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $this->get(route('admin.academic-years.edit', $academicYear))
            ->assertForbidden();
    });
});

describe('Admin AcademicYears Edit - Successful Update', function () {
    it('can update academic year with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
            'is_current' => false,
        ]);

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('year', '2025-2026')
            ->set('start_date', '2025-09-01')
            ->set('end_date', '2026-06-30')
            ->set('is_current', true)
            ->call('update')
            ->assertRedirect(route('admin.academic-years.show', $academicYear));

        expect($academicYear->fresh())
            ->year->toBe('2025-2026')
            ->start_date->format('Y-m-d')->toBe('2025-09-01')
            ->end_date->format('Y-m-d')->toBe('2026-06-30')
            ->is_current->toBeTrue();
    });

    it('dispatches academic-year-updated event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('year', '2025-2026')
            ->set('start_date', '2025-09-01')
            ->set('end_date', '2026-06-30')
            ->call('update')
            ->assertDispatched('academic-year-updated');

        expect($academicYear->fresh()->year)->toBe('2025-2026');
    });

    it('unmarks other current years when marking one as current', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $year1 = AcademicYear::factory()->current()->create([
            'year' => '2023-2024',
            'start_date' => '2023-09-01',
            'end_date' => '2024-06-30',
        ]);
        $year2 = AcademicYear::factory()->create([
            'year' => '2024-2025',
            'start_date' => '2024-09-01',
            'end_date' => '2025-06-30',
        ]);

        Livewire::test(Edit::class, ['academic_year' => $year2])
            ->set('is_current', true)
            ->call('update')
            ->assertRedirect(route('admin.academic-years.show', $year2));

        // Refresh both years from database
        $year1->refresh();
        $year2->refresh();

        expect($year1->is_current)->toBeFalse();
        expect($year2->is_current)->toBeTrue();
    });
});

describe('Admin AcademicYears Edit - Validation', function () {
    it('validates year format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('year', '2024')
            ->call('update')
            ->assertHasErrors(['year']);
    });

    it('validates year uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        AcademicYear::factory()->create(['year' => '2025-2026']);

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('year', '2025-2026')
            ->call('update')
            ->assertHasErrors(['year']);
    });

    it('allows keeping the same year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('start_date', '2024-09-15')
            ->call('update');

        expect($academicYear->fresh()->year)->toBe('2024-2025');
    });

    it('validates that end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Edit::class, ['academic_year' => $academicYear])
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2024-08-01')
            ->call('update')
            ->assertHasErrors(['end_date']);
    });
});
