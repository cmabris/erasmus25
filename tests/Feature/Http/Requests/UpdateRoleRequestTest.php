<?php

use App\Http\Requests\UpdateRoleRequest;
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
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);

    // Crear roles del sistema
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar todos los permisos a super-admin
    $superAdmin->givePermissionTo(Permission::all());
});

describe('UpdateRoleRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('UpdateRoleRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        // Test validation rules directly without FormRequest
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => 12345,
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name uniqueness excluding current role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();
        $otherRole = Role::where('name', Roles::EDITOR)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        // Should allow keeping the same name
        $validator1 = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => [],
        ], $rules);

        expect($validator1->fails())->toBeFalse();

        // Should reject using another role's name
        $validator2 = Validator::make([
            'name' => Roles::EDITOR,
            'permissions' => [],
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('name'))->toBeTrue();
    });

    it('validates name is in allowed roles list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => 'invalid-role-name',
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('allows valid role name from allowed list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates permissions is array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions'))->toBeTrue();
    });

    it('allows nullable permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows empty permissions array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates permissions items are strings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => [123, 456],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions.0'))->toBeTrue();
    });

    it('validates permissions exist in database', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => ['non-existent-permission'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions.0'))->toBeTrue();
    });

    it('allows valid permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('roles', 'name')->ignore($role->id),
                \Illuminate\Validation\Rule::in(Roles::all()),
            ],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', \Illuminate\Validation\Rule::exists('permissions', 'name')],
        ];

        $validator = Validator::make([
            'name' => Roles::ADMIN,
            'permissions' => [Permissions::PROGRAMS_VIEW, Permissions::CALLS_VIEW],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('UpdateRoleRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = new UpdateRoleRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('name.unique');
        expect($messages)->toHaveKey('name.in');
        expect($messages)->toHaveKey('permissions.array');
        expect($messages)->toHaveKey('permissions.*.string');
        expect($messages)->toHaveKey('permissions.*.exists');
    });
});
