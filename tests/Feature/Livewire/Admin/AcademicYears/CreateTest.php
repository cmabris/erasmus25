<?php

use App\Livewire\Admin\AcademicYears\Create;
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

describe('Admin AcademicYears Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.academic-years.create'))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for non-admin users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.academic-years.create'))
            ->assertForbidden();
    });
});

describe('Admin AcademicYears Create - Successful Creation', function () {
    it('can create an academic year with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->set('is_current', false)
            ->call('store')
            ->assertRedirect(route('admin.academic-years.index'));

        expect(AcademicYear::where('year', '2024-2025')->exists())->toBeTrue();
    });

    it('creates academic year with is_current set to true', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->set('is_current', true)
            ->call('store');

        $academicYear = AcademicYear::where('year', '2024-2025')->first();
        expect($academicYear->is_current)->toBeTrue();
    });

    it('unmarks other current years when marking new one as current', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $existingYear = AcademicYear::factory()->current()->create(['year' => '2023-2024']);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->set('is_current', true)
            ->call('store');

        expect($existingYear->fresh()->is_current)->toBeFalse();
        expect(AcademicYear::where('year', '2024-2025')->first()->is_current)->toBeTrue();
    });
});

describe('Admin AcademicYears Create - Validation', function () {
    it('requires year field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->call('store')
            ->assertHasErrors(['year']);
    });

    it('validates year format (YYYY-YYYY)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->call('store')
            ->assertHasErrors(['year']);
    });

    it('validates year uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        AcademicYear::factory()->create(['year' => '2024-2025']);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2025-06-30')
            ->call('store')
            ->assertHasErrors(['year']);
    });

    it('requires start_date field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('end_date', '2025-06-30')
            ->call('store')
            ->assertHasErrors(['start_date']);
    });

    it('requires end_date field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->call('store')
            ->assertHasErrors(['end_date']);
    });

    it('validates that end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024-2025')
            ->set('start_date', '2024-09-01')
            ->set('end_date', '2024-08-01')
            ->call('store')
            ->assertHasErrors(['end_date']);
    });

    it('validates year format in real-time', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('year', '2024')
            ->assertHasErrors(['year']);
    });
});
