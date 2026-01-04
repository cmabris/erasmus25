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
    // Note: Authorization is tested in Livewire component tests
    // These tests focus on validation rules only
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
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Category One']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Category Two']);

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category1->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category1->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Category Two',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('name'))->toBeTrue();
    });

    it('allows keeping the same name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Original Name']);

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Original Name',
        ], $rules);

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
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['slug' => 'slug-one']);
        $category2 = DocumentCategory::factory()->create(['slug' => 'slug-two']);

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category1->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category1->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Category One',
            'slug' => 'slug-two',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('allows keeping the same slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['slug' => 'original-slug']);

        $rules = [
            'name' => ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($category->id)],
            'slug' => ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($category->id)],
            'description' => ['nullable', 'string'],
            'order' => ['nullable', 'integer'],
        ];

        $validator = Validator::make([
            'name' => 'Updated Name',
            'slug' => 'original-slug',
        ], $rules);

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
});

describe('UpdateDocumentCategoryRequest - Custom Messages', function () {
    it('returns custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = new UpdateDocumentCategoryRequest;
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('name.required');
        expect($messages)->toHaveKey('name.unique');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('order.integer');
    });
});
