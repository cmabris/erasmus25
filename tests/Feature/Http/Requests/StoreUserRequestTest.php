<?php

use App\Http\Requests\StoreUserRequest;
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

describe('StoreUserRequest - Authorization', function () {
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
});

describe('StoreUserRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('validates name is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 12345,
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates email is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => str_repeat('a', 250).'@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        User::factory()->create(['email' => 'existing@example.com']);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('allows unique email', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates password is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('validates password is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 12345,
            'password_confirmation' => 12345,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('validates password confirmation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('validates password minimum length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('allows valid password', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates roles is array', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => 'not-an-array',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('allows nullable roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates roles are in allowed list', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['invalid-role'],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });

    it('allows valid roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [Roles::ADMIN, Roles::EDITOR],
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates roles items are strings', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreUserRequest::create('/admin/usuarios', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [123, 456],
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });
});

describe('StoreUserRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = new StoreUserRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('email.required');
        expect($messages)->toHaveKey('email.email');
        expect($messages)->toHaveKey('email.unique');
        expect($messages)->toHaveKey('password.required');
        expect($messages)->toHaveKey('password.confirmed');
        expect($messages)->toHaveKey('roles.array');
        expect($messages)->toHaveKey('roles.*.in');
    });
});
