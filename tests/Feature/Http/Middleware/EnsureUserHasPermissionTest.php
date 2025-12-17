<?php

use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    // Crear una ruta de prueba para el middleware
    Route::middleware(['auth', 'permission:'.Permissions::PROGRAMS_VIEW])
        ->get('/test-permission', fn () => ['message' => 'success']);
});

it('redirects unauthenticated users to login', function () {
    $response = $this->get('/test-permission');

    // El middleware 'auth' redirige a login antes de que el middleware de permisos se ejecute
    $response->assertRedirect('/login');
});

it('returns 403 for authenticated user without required permission', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get('/test-permission');

    $response->assertForbidden();
});

it('allows access for authenticated user with required permission', function () {
    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $user->givePermissionTo($permission);
    $this->actingAs($user);

    $response = $this->get('/test-permission');

    $response->assertSuccessful();
    $response->assertJson(['message' => 'success']);
});

it('allows access for user with role that has required permission', function () {
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);
    $this->actingAs($user);

    $response = $this->get('/test-permission');

    $response->assertSuccessful();
    $response->assertJson(['message' => 'success']);
});

it('allows access when user has any of the specified permissions (OR)', function () {
    Route::middleware(['auth', 'permission:'.Permissions::PROGRAMS_VIEW.'|'.Permissions::PROGRAMS_CREATE])
        ->get('/test-or-permissions', fn () => ['message' => 'success']);

    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $user->givePermissionTo($permission);
    $this->actingAs($user);

    $response = $this->get('/test-or-permissions');

    $response->assertSuccessful();
    $response->assertJson(['message' => 'success']);
});

it('requires all permissions when multiple middleware are used (AND)', function () {
    Route::middleware(['auth', 'permission:'.Permissions::PROGRAMS_VIEW, 'permission:'.Permissions::PROGRAMS_CREATE])
        ->get('/test-and-permissions', fn () => ['message' => 'success']);

    $user = User::factory()->create();
    $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $user->givePermissionTo($permission);
    $this->actingAs($user);

    $response = $this->get('/test-and-permissions');

    $response->assertForbidden();
});

it('allows access when user has all required permissions (AND)', function () {
    Route::middleware(['auth', 'permission:'.Permissions::PROGRAMS_VIEW, 'permission:'.Permissions::PROGRAMS_CREATE])
        ->get('/test-and-permissions', fn () => ['message' => 'success']);

    $user = User::factory()->create();
    $permission1 = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $permission2 = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    $user->givePermissionTo([$permission1, $permission2]);
    $this->actingAs($user);

    $response = $this->get('/test-and-permissions');

    $response->assertSuccessful();
    $response->assertJson(['message' => 'success']);
});

it('allows access for super-admin role with all permissions', function () {
    $user = User::factory()->create();
    $role = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    $role->givePermissionTo($permission);
    $user->assignRole($role);
    $this->actingAs($user);

    $response = $this->get('/test-permission');

    $response->assertSuccessful();
    $response->assertJson(['message' => 'success']);
});
