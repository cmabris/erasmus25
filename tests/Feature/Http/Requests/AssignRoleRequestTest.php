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
    it('authorizes user with edit permission to assign roles to other user', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to assign roles', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
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

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies user from assigning roles to themselves', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$user->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($user) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $user); // Mismo usuario

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
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

        $request = AssignRoleRequest::create(
            '/admin/usuarios/999/roles',
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
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

        $request = AssignRoleRequest::create(
            '/admin/usuarios/999/roles',
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('AssignRoleRequest - Validation Rules', function () {
    it('validates roles is required', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('validates roles is array', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => 'not-an-array']
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => 'not-an-array'], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('validates roles items are strings', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [123, 456]]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => [123, 456]], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });

    it('validates roles are in allowed list', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => ['invalid-role']]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => ['invalid-role']], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.0'))->toBeTrue();
    });

    it('allows valid roles', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN, Roles::EDITOR]]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => [Roles::ADMIN, Roles::EDITOR]], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows single role', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN]]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => [Roles::ADMIN]], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows all valid roles', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => Roles::all()]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => Roles::all()], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('rejects empty array', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => []]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => []], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles'))->toBeTrue();
    });

    it('rejects mixed valid and invalid roles', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            ['roles' => [Roles::ADMIN, 'invalid-role']]
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make(['roles' => [Roles::ADMIN, 'invalid-role']], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('roles.1'))->toBeTrue();
    });
});

describe('AssignRoleRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('roles.required');
        expect($messages)->toHaveKey('roles.array');
        expect($messages)->toHaveKey('roles.*.string');
        expect($messages)->toHaveKey('roles.*.in');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['roles.required'])->toBe(__('Debe seleccionar al menos un rol.'));
        expect($messages['roles.array'])->toBe(__('Los roles deben ser un array.'));
        expect($messages['roles.*.string'])->toBe(__('Cada rol debe ser un texto válido.'));
        expect($messages['roles.*.in'])->toBe(__('Uno o más roles seleccionados no son válidos. Los roles válidos son: :values', ['values' => implode(', ', Roles::all())]));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::USERS_EDIT);
        $this->actingAs($user);

        $testUser = User::factory()->create();

        $request = AssignRoleRequest::create(
            "/admin/usuarios/{$testUser->id}/roles",
            'POST',
            []
        );
        $request->setRouteResolver(function () use ($testUser) {
            $route = new \Illuminate\Routing\Route(['POST'], '/admin/usuarios/{user}/roles', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('user', $testUser);

            return $route;
        });

        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('roles'))->toBe(__('Debe seleccionar al menos un rol.'));
    });
});
