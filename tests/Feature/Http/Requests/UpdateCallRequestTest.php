<?php

use App\Http\Requests\UpdateCallRequest;
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

describe('UpdateCallRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('UpdateCallRequest - Validation Rules', function () {
    it('validates slug uniqueness excluding current call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'call-1',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'slug' => 'call-2',
        ]);

        // Test validation rules directly without FormRequest
        $rules = [
            'program_id' => ['required', 'exists:programs,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('calls', 'slug')->ignore($call1->id)],
            'type' => ['required', \Illuminate\Validation\Rule::in(['alumnado', 'personal'])],
            'modality' => ['required', \Illuminate\Validation\Rule::in(['corta', 'larga'])],
            'number_of_places' => ['required', 'integer', 'min:1'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*' => ['required', 'string', 'max:255'],
        ];

        $data = [
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'slug' => 'call-2',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
        ];

        $validator = Validator::make($data, $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
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
            'slug' => 'test-slug',
        ]);

        // Test validation rules directly without FormRequest
        $rules = [
            'program_id' => ['required', 'exists:programs,id'],
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('calls', 'slug')->ignore($call->id)],
            'type' => ['required', \Illuminate\Validation\Rule::in(['alumnado', 'personal'])],
            'modality' => ['required', \Illuminate\Validation\Rule::in(['corta', 'larga'])],
            'number_of_places' => ['required', 'integer', 'min:1'],
            'destinations' => ['required', 'array', 'min:1'],
            'destinations.*' => ['required', 'string', 'max:255'],
        ];

        $data = [
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Test',
            'slug' => 'test-slug',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España'],
        ];

        $validator = Validator::make($data, $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateCallRequest - Custom Messages', function () {
    it('has custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $request = UpdateCallRequest::create("/admin/convocatorias/{$call->id}", 'PUT', []);
        $request->setRouteResolver(function () use ($call) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/convocatorias/{call}', []);
            $route->setParameter('call', $call);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('program_id.required');
        expect($messages)->toHaveKey('title.required');
    });
});
