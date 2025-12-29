<?php

use App\Http\Requests\StoreCallRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
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
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::CALLS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_EDIT,
        Permissions::CALLS_DELETE,
        Permissions::CALLS_PUBLISH,
    ]);
});

describe('StoreCallRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('StoreCallRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('program_id'))->toBeTrue();
        expect($validator->errors()->has('academic_year_id'))->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
        expect($validator->errors()->has('type'))->toBeTrue();
        expect($validator->errors()->has('modality'))->toBeTrue();
        expect($validator->errors()->has('number_of_places'))->toBeTrue();
        expect($validator->errors()->has('destinations'))->toBeTrue();
    });

    it('validates program_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => 99999,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('program_id'))->toBeTrue();
    });

    it('validates type is valid enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'type' => 'invalid',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('type'))->toBeTrue();
    });

    it('validates destinations is array with at least one item', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('destinations'))->toBeTrue();
    });

    it('validates estimated_end_date is after estimated_start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
            'estimated_start_date' => '2025-06-01',
            'estimated_end_date' => '2025-05-01',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('estimated_end_date'))->toBeTrue();
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        Call::factory()->create(['slug' => 'test-slug']);

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'slug' => 'test-slug',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });
});

describe('StoreCallRequest - Custom Messages', function () {
    it('has custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallRequest::create('/admin/convocatorias', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('program_id.required');
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('destinations.required');
    });
});
