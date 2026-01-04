<?php

use App\Livewire\Admin\Documents\Create;
use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
        Permissions::DOCUMENTS_DELETE,
    ]);
    $viewer->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
    ]);
});

describe('Admin Documents Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.documents.create'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with DOCUMENTS_CREATE permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_CREATE);
        $this->actingAs($user);

        $this->get(route('admin.documents.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.documents.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without DOCUMENTS_CREATE permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.documents.create'))
            ->assertForbidden();
    });
});

describe('Admin Documents Create - Successful Creation', function () {
    it('can create a document with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('slug', 'documento-test')
            ->set('description', 'Descripción del documento')
            ->set('documentType', 'convocatoria')
            ->set('version', '1.0')
            ->set('isActive', true)
            ->call('store')
            ->assertDispatched('document-created');

        expect(Document::where('title', 'Documento Test')->exists())->toBeTrue();
    });

    it('sets created_by to authenticated user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store');

        $document = Document::where('title', 'Documento Test')->first();
        expect($document->created_by)->toBe($user->id);
    });

    it('can create document with optional program and academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('programId', $program->id)
            ->set('academicYearId', $academicYear->id)
            ->set('title', 'Documento con Programa')
            ->set('documentType', 'convocatoria')
            ->call('store');

        $document = Document::where('title', 'Documento con Programa')->first();
        expect($document->program_id)->toBe($program->id)
            ->and($document->academic_year_id)->toBe($academicYear->id);
    });

    it('can create document with file upload', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        // Create a temporary PDF file with actual content
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $pdf = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        // Override the fake file with real content
        file_put_contents($pdf->getRealPath(), '%PDF-1.4 Test PDF content');

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento con Archivo')
            ->set('documentType', 'convocatoria')
            ->set('file', $pdf)
            ->call('store');

        $document = Document::where('title', 'Documento con Archivo')->first();
        expect($document)->not->toBeNull();
        expect($document->hasMedia('file'))->toBeTrue();

        // Clean up
        @unlink($tempFile);
    });

    it('dispatches document-created event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertDispatched('document-created');
    });

    it('redirects to show page after creation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $component = Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store');

        $document = Document::where('title', 'Documento Test')->first();
        $component->assertRedirect(route('admin.documents.show', $document));
    });
});

describe('Admin Documents Create - Validation', function () {
    it('requires category_id', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->set('categoryId', null)
            ->call('store')
            ->assertHasErrors(['categoryId']);
    });

    it('requires title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', '')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['title']);
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', str_repeat('a', 256))
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['title']);
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('slug', str_repeat('a', 256))
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['slug' => 'existing-slug', 'category_id' => $category->id]);

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'New Document')
            ->set('slug', 'existing-slug')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('requires document_type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', '')
            ->call('store')
            ->assertHasErrors(['documentType']);
    });

    it('validates document_type is in allowed values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'invalid_type')
            ->call('store')
            ->assertHasErrors(['documentType']);
    });

    it('validates category_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('categoryId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['categoryId']);
    });

    it('validates program_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('programId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['programId']);
    });

    it('validates academic_year_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('academicYearId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('store')
            ->assertHasErrors(['academicYearId']);
    });

    it('validates file is valid file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-msdownload');

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->set('file', $file)
            ->call('store')
            ->assertHasErrors(['file']);
    });

    it('validates file size is within limit', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        // Create a file larger than 20MB (20480 KB)
        $pdf = UploadedFile::fake()->create('large.pdf', 21000, 'application/pdf');

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->set('file', $pdf)
            ->call('store')
            ->assertHasErrors(['file']);
    });

    it('accepts valid file types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        // Test PDF
        $pdf = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');
        $tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        file_put_contents($pdf->getRealPath(), '%PDF-1.4 Test PDF content');

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento PDF')
            ->set('documentType', 'convocatoria')
            ->set('file', $pdf)
            ->call('store');

        $document = Document::where('title', 'Documento PDF')->first();
        expect($document)->not->toBeNull();
        expect($document->hasMedia('file'))->toBeTrue();

        @unlink($tempFile);
    });
});

describe('Admin Documents Create - Slug Generation', function () {
    it('automatically generates slug from title when title changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('title', 'Documento Test Name');

        expect($component->get('slug'))->toBe('documento-test-name');
    });

    it('does not override custom slug when title changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('slug', 'custom-slug')
            ->set('title', 'New Title');

        expect($component->get('slug'))->toBe('custom-slug');
    });

    it('generates slug automatically when creating document', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento con Slug Automático')
            ->set('documentType', 'convocatoria')
            ->call('store');

        $document = Document::where('title', 'Documento con Slug Automático')->first();
        expect($document->slug)->toBe('documento-con-slug-automatico');
    });

    it('uses provided slug if given', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Create::class)
            ->set('categoryId', $category->id)
            ->set('title', 'Documento Test')
            ->set('slug', 'custom-slug')
            ->set('documentType', 'convocatoria')
            ->call('store');

        $document = Document::where('title', 'Documento Test')->first();
        expect($document->slug)->toBe('custom-slug');
    });
});

describe('Admin Documents Create - Computed Properties', function () {
    it('loads categories for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Categoría A', 'slug' => 'categoria-a']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Categoría B', 'slug' => 'categoria-b']);

        $component = Livewire::test(Create::class);

        $categories = $component->get('categories');
        expect($categories->pluck('name')->toArray())->toContain('Categoría A')
            ->and($categories->pluck('name')->toArray())->toContain('Categoría B');
    });

    it('loads programs for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa A']);
        $program2 = Program::factory()->create(['name' => 'Programa B']);

        $component = Livewire::test(Create::class);

        $programs = $component->get('programs');
        expect($programs->pluck('name')->toArray())->toContain('Programa A')
            ->and($programs->pluck('name')->toArray())->toContain('Programa B');
    });

    it('loads academic years for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear1 = AcademicYear::factory()->create(['year' => '2023-2024']);
        $academicYear2 = AcademicYear::factory()->create(['year' => '2024-2025']);

        $component = Livewire::test(Create::class);

        $academicYears = $component->get('academicYears');
        expect($academicYears->pluck('year')->toArray())->toContain('2023-2024')
            ->and($academicYears->pluck('year')->toArray())->toContain('2024-2025');
    });
});
