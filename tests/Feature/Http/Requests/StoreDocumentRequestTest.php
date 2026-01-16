<?php

use App\Http\Requests\StoreDocumentRequest;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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

describe('StoreDocumentRequest - Authorization', function () {
    it('authorizes user with create permission to create document', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_CREATE);
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('authorizes super-admin user to create document', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeTrue();
    });

    it('denies user without create permission', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW); // Solo view, no create
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $request->setUserResolver(fn () => $user);

        expect($request->authorize())->toBeFalse();
    });

    it('denies unauthenticated user', function () {
        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $request->setUserResolver(fn () => null);

        expect($request->authorize())->toBeFalse();
    });
});

describe('StoreDocumentRequest - Validation Rules', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('category_id'))->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
        expect($validator->errors()->has('document_type'))->toBeTrue();
    });

    it('validates category_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => 99999,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('category_id'))->toBeTrue();
    });

    it('validates program_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'program_id' => 99999,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('program_id'))->toBeTrue();
    });

    it('validates academic_year_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'academic_year_id' => 99999,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('academic_year_id'))->toBeTrue();
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => str_repeat('a', 256),
            'document_type' => 'convocatoria',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('title'))->toBeTrue();
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['slug' => 'test-slug', 'category_id' => $category->id]);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'slug' => 'test-slug',
            'document_type' => 'convocatoria',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('slug'))->toBeTrue();
    });

    it('validates document_type is valid enum', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'invalid_type',
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('document_type'))->toBeTrue();
    });

    it('validates file is valid file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-msdownload');

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'file' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('file'))->toBeTrue();
    });

    it('validates file size is within limit', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        // Create a file larger than 20MB (20480 KB)
        $file = UploadedFile::fake()->create('large.pdf', 21000, 'application/pdf');

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'file' => $file,
        ], $rules);

        expect($validator->fails())->toBeTrue();
        expect($validator->errors()->has('file'))->toBeTrue();
    });

    it('accepts valid file types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        // Test PDF
        $pdf = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'file' => $pdf,
        ], $rules);

        expect($validator->fails())->toBeFalse();

        // Test Word
        $doc = UploadedFile::fake()->create('document.doc', 100, 'application/msword');
        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'file' => $doc,
        ], $rules);

        expect($validator->fails())->toBeFalse();

        // Test Image
        $image = UploadedFile::fake()->image('document.jpg', 100, 100);
        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'file' => $image,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });

    it('allows nullable optional fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $rules = $request->rules();

        $validator = Validator::make([
            'category_id' => $category->id,
            'title' => 'Test Document',
            'document_type' => 'convocatoria',
            'program_id' => null,
            'academic_year_id' => null,
            'slug' => null,
            'description' => null,
            'version' => null,
            'file' => null,
        ], $rules);

        expect($validator->fails())->toBeFalse();
    });
});

describe('StoreDocumentRequest - Custom Messages', function () {
    it('has custom error messages', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $request = StoreDocumentRequest::create('/admin/documentos', 'POST', []);
        $messages = $request->messages();

        expect($messages)->toBeArray();
        expect($messages)->toHaveKey('category_id.required');
        expect($messages)->toHaveKey('title.required');
        expect($messages)->toHaveKey('document_type.required');
        expect($messages)->toHaveKey('slug.unique');
        expect($messages)->toHaveKey('file.mimes');
        expect($messages)->toHaveKey('file.max');
    });
});
