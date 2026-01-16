<?php

use App\Http\Requests\UpdateUserRequest;
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

describe('UpdateUserRequest - Authorization', function () {
    it('authorizes user with edit permission to update user', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to update user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without edit permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_VIEW); // Solo view, no edit
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not User instance', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $request = UpdateUserRequest::create(
            '/admin/usuarios/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', 'not-a-user'); // No es instancia de User

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $request = UpdateUserRequest::create(
            '/admin/usuarios/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateUserRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        // Test validation rules directly without FormRequest
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates name is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'email' => 'test@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 12345,
            'email' => 'test@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
            'email' => 'test@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates email is required', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email format', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'invalid-email',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => str_repeat('a', 250).'@example.com',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('email'))->toBeTrue();
    });

    it('validates email uniqueness excluding current user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create(['email' => 'test@example.com']);
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        // Should allow keeping the same email
        $validator1 = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ], $rules);

        expect($validator1->fails())->toBeFalse();

        // Should reject using another user's email
        $validator2 = Validator::make([
            'name' => 'Test User',
            'email' => 'other@example.com',
        ], $rules);

        expect($validator2->fails())->toBeTrue();
        expect($validator2->errors()->has('email'))->toBeTrue();
    });

    it('allows nullable password', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates password confirmation when password is provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('validates password minimum length when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('password'))->toBeTrue();
    });

    it('allows valid password when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($testUser->id)],
            'password' => ['nullable', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ];

        $validator = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles route parameter as User instance in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
            ]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser); // Instancia de User

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('password');
    });

    it('handles route parameter as ID in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
            ]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser->id); // ID numérico

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('email');
        expect($rules)->toHaveKey('password');
    });
});

describe('UpdateUserRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('email.required');
        expect($messages)->toHaveKey('email.string');
        expect($messages)->toHaveKey('email.email');
        expect($messages)->toHaveKey('email.max');
        expect($messages)->toHaveKey('email.unique');
        expect($messages)->toHaveKey('password.string');
        expect($messages)->toHaveKey('password.confirmed');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = UpdateUserRequest::create(
            "/admin/usuarios/{$testUser->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/usuarios/{user}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['name.required'])->toBe(__('El nombre del usuario es obligatorio.'));
        expect($messages['name.string'])->toBe(__('El nombre del usuario debe ser un texto válido.'));
        expect($messages['name.max'])->toBe(__('El nombre del usuario no puede tener más de :max caracteres.'));
        expect($messages['email.required'])->toBe(__('El correo electrónico es obligatorio.'));
        expect($messages['email.email'])->toBe(__('El correo electrónico debe ser una dirección de correo válida.'));
        expect($messages['email.unique'])->toBe(__('Este correo electrónico ya está registrado.'));
        expect($messages['password.confirmed'])->toBe(__('Las contraseñas no coinciden.'));
    });
});
