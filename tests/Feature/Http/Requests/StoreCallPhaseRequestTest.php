<?php

use App\Http\Requests\StoreCallPhaseRequest;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
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
    ]);
});

describe('StoreCallPhaseRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('StoreCallPhaseRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_id'))->toBeTrue();
        expect($validator->errors()->has('phase_type'))->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates call_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => 99999,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('call_id'))->toBeTrue();
    });

    it('validates phase_type is valid enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'invalid_type',
            'name' => 'Fase Test',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('phase_type'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => str_repeat('a', 256), // Exceeds max 255
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'start_date' => '2024-01-31',
            'end_date' => '2024-01-01',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('end_date'))->toBeTrue();
    });

    it('validates order uniqueness per call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        CallPhase::factory()->create([
            'call_id' => $call->id,
            'order' => 5,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', [
            'call_id' => $call->id,
            'order' => 5,
        ]);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'order' => 5,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('order'))->toBeTrue();
    });

    it('validates only one phase can be current per call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        CallPhase::factory()->create([
            'call_id' => $call->id,
            'is_current' => true,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', [
            'call_id' => $call->id,
            'is_current' => true,
        ]);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'is_current' => true,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('is_current'))->toBeTrue();
    });

    it('allows creating phase with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'call_id' => $call->id,
            'phase_type' => 'publicacion',
            'name' => 'Fase Test',
            'description' => 'Descripción de prueba',
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'is_current' => false,
            'order' => 1,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreCallPhaseRequest - Custom Messages', function () {
    it('has custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('call_id.required');
        expect($messages)->toHaveKey('phase_type.required');
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('end_date.after');
        expect($messages)->toHaveKey('order.unique');
    });

    it('has custom attribute names', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreCallPhaseRequest::create('/admin/convocatorias/1/fases', 'POST', []);
        $attributes = $request->attributes();

        expect($attributes)->toBeArray();
        expect($attributes)->toHaveKey('call_id');
        expect($attributes)->toHaveKey('phase_type');
        expect($attributes)->toHaveKey('name');
        expect($attributes)->toHaveKey('order');
    });
});
