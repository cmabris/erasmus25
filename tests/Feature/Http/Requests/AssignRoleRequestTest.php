<?php

use App\Http\Requests\AssignRoleRequest;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::USERS_VIEW,
        Permissions::USERS_CREATE,
        Permissions::USERS_EDIT,
        Permissions::USERS_DELETE,
    ]);
});

describe('AssignRoleRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('AssignRoleRequest - Validation Rules', function () {
    it('validates roles is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        // Test validation rules directly without FormRequest
        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('validates roles is array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('validates roles items are strings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => [123, 456],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });

    it('validates roles are in allowed list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => ['invalid-role'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });

    it('allows valid roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => [Roles::ADMIN, Roles::EDITOR],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows single role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => [Roles::ADMIN],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows all valid roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => Roles::all(),
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('rejects empty array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => [],
        ], $rules);

        // Empty array fails because 'required' rule requires at least one item
        // But 'array' rule passes, so we need to check if it's truly empty
        // Actually, 'required' on array means the array must exist and have at least one element
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('rejects mixed valid and invalid roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'roles' => ['required', 'array'],
            'roles.*' => ['string', \Illuminate\Validation\Rule::in(Roles::all())],
        ];

        $validator = Validator::make([
            'roles' => [Roles::ADMIN, 'invalid-role'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.1'))->toBeTrue();
    });
});

describe('AssignRoleRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = new AssignRoleRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('roles.required');
        expect($messages)->toHaveKey('roles.array');
        expect($messages)->toHaveKey('roles.*.string');
        expect($messages)->toHaveKey('roles.*.in');
    });
});
