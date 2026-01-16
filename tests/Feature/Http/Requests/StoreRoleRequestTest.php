<?php

use App\Http\Requests\StoreRoleRequest;
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

describe('StoreRoleRequest - Authorization', function () {
    it('authorizes super-admin user to create role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies non-super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreRoleRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

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

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

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

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Role already exists from beforeEach, so validation should fail
        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::ADMIN, // This role already exists
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is in allowed roles list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

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

        // Use a role name that doesn't exist yet (but is in the allowed list)
        // Since all system roles exist in beforeEach, we need to delete one temporarily
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates permissions is array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions'))->toBeTrue();
    });

    it('allows nullable permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows empty permissions array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => [],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates permissions items are strings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => [123, 456],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions.0'))->toBeTrue();
    });

    it('validates permissions exist in database', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => ['non-existent-permission'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('permissions.0'))->toBeTrue();
    });

    it('allows valid permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => [Permissions::PROGRAMS_VIEW, Permissions::CALLS_VIEW],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows multiple valid permissions', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        // Use a role that doesn't exist yet
        Role::where('name', Roles::VIEWER)->delete();

        $request = StoreRoleRequest::create('/admin/roles', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => Roles::VIEWER,
            'permissions' => [
                Permissions::PROGRAMS_VIEW,
                Permissions::PROGRAMS_CREATE,
                Permissions::CALLS_VIEW,
            ],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreRoleRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = new StoreRoleRequest;
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
