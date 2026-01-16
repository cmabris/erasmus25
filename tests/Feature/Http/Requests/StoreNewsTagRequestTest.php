<?php

use App\Http\Requests\StoreNewsTagRequest;
use App\Models\NewsTag;
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
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
    ]);

    // Editor tiene permisos limitados (sin delete)
    $editor->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
    ]);
});

describe('StoreNewsTagRequest - Authorization', function () {
    it('authorizes user with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create(
            '/admin/etiquetas-noticias',
            'POST',
            [
                'name' => 'Test Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create(
            '/admin/etiquetas-noticias',
            'POST',
            [
                'name' => 'Test Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes editor user with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create(
            '/admin/etiquetas-noticias',
            'POST',
            [
                'name' => 'Test Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies viewer user without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create(
            '/admin/etiquetas-noticias',
            'POST',
            [
                'name' => 'Test Tag',
            ]
        );
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreNewsTagRequest::create(
            '/admin/etiquetas-noticias',
            'POST',
            [
                'name' => 'Test Tag',
            ]
        );
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreNewsTagRequest - Validation Rules', function () {
    it('validates required name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 12345, // No es string
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => str_repeat('a', 256), // Más de 255 caracteres
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['name' => 'Existing Tag']);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Existing Tag', // Ya existe
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates slug is string when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test Tag',
            'slug' => 12345, // No es string
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates slug max length when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test Tag',
            'slug' => str_repeat('a', 256), // Más de 255 caracteres
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates slug uniqueness when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['slug' => 'existing-slug']);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test Tag',
            'slug' => 'existing-slug', // Ya existe
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('accepts valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('accepts valid data without slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'name' => 'Test Tag',
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreNewsTagRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('name.unique');
        expect($messages)->toHaveKey('slug.string');
        expect($messages)->toHaveKey('slug.max');
        expect($messages)->toHaveKey('slug.unique');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $messages = $request->messages();

        expect($messages['name.required'])->toBe(__('El nombre de la etiqueta es obligatorio.'));
        expect($messages['name.string'])->toBe(__('El nombre de la etiqueta debe ser un texto válido.'));
        expect($messages['name.max'])->toBe(__('El nombre de la etiqueta no puede tener más de :max caracteres.'));
        expect($messages['name.unique'])->toBe(__('Esta etiqueta ya existe.'));
        expect($messages['slug.string'])->toBe(__('El slug debe ser un texto válido.'));
        expect($messages['slug.max'])->toBe(__('El slug no puede tener más de :max caracteres.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
    });

    it('uses custom messages in validation errors', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreNewsTagRequest::create('/admin/etiquetas-noticias', 'POST', []);
        $rules = $request->rules();
        $messages = $request->messages();

        $validator = Validator::make([], $rules, $messages);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->first('name'))->toBe(__('El nombre de la etiqueta es obligatorio.'));
    });
});
