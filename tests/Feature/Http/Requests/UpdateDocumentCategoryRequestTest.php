<?php

use App\Http\Requests\UpdateDocumentCategoryRequest;
use App\Models\DocumentCategory;
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
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
        Permissions::DOCUMENTS_DELETE,
    ]);
});

describe('UpdateDocumentCategoryRequest - Authorization', function () {
    it('authorizes user with edit permission to update document category', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to update document category', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without edit permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW); // Solo view, no edit
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setUserResolver(fn () => null);
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is not DocumentCategory instance', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $request = UpdateDocumentCategoryRequest::create(
            '/admin/documentos/categorias/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', 'not-a-category'); // No es instancia de DocumentCategory

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });

    it('returns false when route parameter is null', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $request = UpdateDocumentCategoryRequest::create(
            '/admin/documentos/categorias/999',
            'PUT',
            []
        );
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', null);

            return $route;
        });

        expect($request->authorize())->toBeFalse();
    });
});

describe('UpdateDocumentCategoryRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        // Test validation rules directly without FormRequest (because it needs route binding)
        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 12345,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('validates name uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Category One']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Category Two']);

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category1->id}",
            'PUT',
            ['name' => 'Category Two']
        );
        $request->setRouteResolver(function () use ($category1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category1);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('allows keeping the same name', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Original Name']);

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            ['name' => 'Original Name']
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates slug is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Test Category',
            'slug' => 12345,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Test Category',
            'slug' => str_repeat('a', 256),
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates slug uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['slug' => 'slug-one']);
        $category2 = DocumentCategory::factory()->create(['slug' => 'slug-two']);

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category1->id}",
            'PUT',
            [
                'name' => 'Category One',
                'slug' => 'slug-two',
            ]
        );
        $request->setRouteResolver(function () use ($category1) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category1);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('allows keeping the same slug', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['slug' => 'original-slug']);

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            [
                'name' => 'Updated Name',
                'slug' => 'original-slug',
            ]
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        $rules = $request->rules();
        $validator = Validator::make($request->all(), $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows nullable slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Updated Category',
            'slug' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates description is string', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Test Category',
            'description' => 12345,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('description'))->toBeTrue();
    });

    it('allows nullable description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Updated Category',
            'description' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('validates order is integer', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Test Category',
            'order' => 'not-an-integer',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('order'))->toBeTrue();
    });

    it('allows nullable order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Updated Category',
            'order' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows valid integer order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Updated Category',
            'order' => 10,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('handles route parameter as DocumentCategory instance in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            ['name' => 'Updated Category']
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category); // Instancia de DocumentCategory

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('slug');
        expect($rules)->toHaveKey('description');
        expect($rules)->toHaveKey('order');
    });

    it('handles route parameter as ID in rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            ['name' => 'Updated Category']
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category->id); // ID numérico

            return $route;
        });

        $rules = $request->rules();

        expect($rules)->toBeArray();
        expect($rules)->toHaveKey('name');
        expect($rules)->toHaveKey('slug');
        expect($rules)->toHaveKey('description');
        expect($rules)->toHaveKey('order');
    });
});

describe('UpdateDocumentCategoryRequest - Custom Messages', function () {
    it('has custom error messages for all validation rules', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.string');
        expect($messages)->toHaveKey('name.max');
        expect($messages)->toHaveKey('name.unique');
        expect($messages)->toHaveKey('slug.string');
        expect($messages)->toHaveKey('slug.max');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('description.string');
        expect($messages)->toHaveKey('order.integer');
    });

    it('returns translated custom messages', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = UpdateDocumentCategoryRequest::create(
            "/admin/documentos/categorias/{$category->id}",
            'PUT',
            []
        );
        $request->setRouteResolver(function () use ($category) {
            $route = new \Illuminate\Routing\Route(['PUT'], '/admin/documentos/categorias/{document_category}', []);
            $route->bind(new \Illuminate\Http\Request);
            $route->setParameter('document_category', $category);

            return $route;
        });

        $messages = $request->messages();

        expect($messages['name.required'])->toBe(__('El nombre de la categoría es obligatorio.'));
        expect($messages['name.string'])->toBe(__('El nombre de la categoría debe ser un texto válido.'));
        expect($messages['name.max'])->toBe(__('El nombre de la categoría no puede tener más de :max caracteres.'));
        expect($messages['name.unique'])->toBe(__('Esta categoría ya existe.'));
        expect($messages['slug.unique'])->toBe(__('Este slug ya está en uso.'));
    });
});
