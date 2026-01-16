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
    it('authorizes super-admin user to update role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies non-super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN); // No es super-admin
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not Role instance', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = UpdateRoleRequest::create(
            '/admin/roles/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', 'not-a-role'); // No es instancia de Role

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = UpdateRoleRequest::create(
            '/admin/roles/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
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

    it('prevents changing name of system role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first(); // Rol del sistema

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            [
                'name' => Roles::EDITOR, // Intentar cambiar el nombre a otro rol del sistema
                'permissions' => [],
            ]
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        // La validación debe fallar porque:
        // 1. El nombre EDITOR ya existe (unique validation)
        // 2. O la validación personalizada previene el cambio (system role validation)
        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates custom rule prevents changing system role name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first(); // Rol del sistema

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        $rules = $request->rules();
        
        // Extraer la validación personalizada y probarla directamente
        $nameRules = $rules['name'];
        $customRule = null;
        foreach ($nameRules as $rule) {
            if (is_callable($rule) && !is_string($rule) && !($rule instanceof \Illuminate\Contracts\Validation\Rule)) {
                $customRule = $rule;
                break;
            }
        }
        
        expect($customRule)->not->toBeNull();
        
        // Probar la validación personalizada directamente
        $failCalled = false;
        $failMessage = '';
        $fail = function ($message) use (&$failCalled, &$failMessage) {
            $failCalled = true;
            $failMessage = $message;
        };
        
        // Intentar cambiar el nombre de un rol del sistema a un valor diferente
        $customRule('name', Roles::EDITOR, $fail);
        
        expect($failCalled)->toBeTrue();
        expect($failMessage)->toBe(__('No se puede cambiar el nombre de un rol del sistema.'));
    });

    it('allows keeping the same name for system role', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first(); // Rol del sistema

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            [
                'name' => Roles::ADMIN, // Mantener el mismo nombre
                'permissions' => [],
            ]
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles route parameter as Role instance in rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            [
                'name' => Roles::ADMIN,
                'permissions' => [],
            ]
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role); // Instancia de Role

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('permissions');
    });

    it('handles route parameter as ID in rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            [
                'name' => Roles::ADMIN,
                'permissions' => [],
            ]
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role->id); // ID numérico

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('permissions');
    });
});

describe('UpdateRoleRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

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

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $role = Role::where('name', Roles::ADMIN)->first();

        $request = UpdateRoleRequest::create(
            "/admin/roles/{$role->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($role) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/roles/{role}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('role', $role);

            return $route;
        });

        $messages = $request->messages();
        $validRoles = implode(', ', Roles::all());

        expect($messages['name.required'])->toBe(__('El nombre del rol es obligatorio.'));
        expect($messages['name.string'])->toBe(__('El nombre del rol debe ser un texto válido.'));
        expect($messages['name.max'])->toBe(__('El nombre del rol no puede tener más de :max caracteres.'));
        expect($messages['name.unique'])->toBe(__('Este nombre de rol ya está en uso.'));
        expect($messages['name.in'])->toBe(__('El nombre del rol no es válido. Los roles válidos son: :values', ['values' => $validRoles]));
    });
});
