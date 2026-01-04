<?php

use App\Livewire\Admin\Documents\Edit;
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

describe('Admin Documents Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.edit', $document))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with DOCUMENTS_EDIT permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.edit', $document))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.edit', $document))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without DOCUMENTS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.edit', $document))
            ->assertForbidden();
    });
});

describe('Admin Documents Edit - Data Loading', function () {
    it('loads document data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Categoría Original']);
        $program = Program::factory()->create(['name' => 'Programa Original']);
        $academicYear = AcademicYear::factory()->create(['year' => '2023-2024']);

        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Documento Original',
            'slug' => 'documento-original',
            'description' => 'Descripción original',
            'document_type' => 'convocatoria',
            'version' => '1.0',
            'is_active' => true,
        ]);

        Livewire::test(Edit::class, ['document' => $document])
            ->assertSet('categoryId', $category->id)
            ->assertSet('programId', $program->id)
            ->assertSet('academicYearId', $academicYear->id)
            ->assertSet('title', 'Documento Original')
            ->assertSet('slug', 'documento-original')
            ->assertSet('description', 'Descripción original')
            ->assertSet('documentType', 'convocatoria')
            ->assertSet('version', '1.0')
            ->assertSet('isActive', true);
    });

    it('loads document with null optional fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => null,
            'academic_year_id' => null,
            'description' => null,
            'version' => null,
        ]);

        Livewire::test(Edit::class, ['document' => $document])
            ->assertSet('programId', null)
            ->assertSet('academicYearId', null)
            ->assertSet('description', '')
            ->assertSet('version', '');
    });
});

describe('Admin Documents Edit - Successful Update', function () {
    it('can update document with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Categoría Original', 'slug' => 'categoria-original']);
        $newCategory = DocumentCategory::factory()->create(['name' => 'Categoría Nueva', 'slug' => 'categoria-nueva']);
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'Documento Original',
            'document_type' => 'convocatoria',
        ]);

        $response = Livewire::test(Edit::class, ['document' => $document])
            ->set('categoryId', $newCategory->id)
            ->set('title', 'Documento Actualizado')
            ->set('description', 'Nueva descripción')
            ->set('documentType', 'guia')
            ->set('version', '2.0')
            ->set('isActive', false)
            ->call('update')
            ->assertDispatched('document-updated')
            ->assertRedirect(route('admin.documents.show', $document));

        expect($document->fresh())
            ->category_id->toBe($newCategory->id)
            ->title->toBe('Documento Actualizado')
            ->description->toBe('Nueva descripción')
            ->document_type->toBe('guia')
            ->version->toBe('2.0')
            ->is_active->toBeFalse();
    });

    it('sets updated_by to authenticated user', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Actualizado')
            ->call('update');

        expect($document->fresh()->updated_by)->toBe($user->id);
    });

    it('can update document with new program and academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => null,
            'academic_year_id' => null,
        ]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('programId', $program->id)
            ->set('academicYearId', $academicYear->id)
            ->set('title', $document->title)
            ->set('documentType', $document->document_type)
            ->call('update');

        $document->refresh();
        expect($document->program_id)->toBe($program->id)
            ->and($document->academic_year_id)->toBe($academicYear->id);
    });

    it('can remove program and academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('programId', null)
            ->set('academicYearId', null)
            ->set('title', $document->title)
            ->set('documentType', $document->document_type)
            ->call('update');

        $document->refresh();
        expect($document->program_id)->toBeNull()
            ->and($document->academic_year_id)->toBeNull();
    });

    it('can update document with new file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a temporary PDF file with actual content
        $pdf = UploadedFile::fake()->create('new-document.pdf', 100, 'application/pdf');
        file_put_contents($pdf->getRealPath(), '%PDF-1.4 Test PDF content');

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', $document->title)
            ->set('documentType', $document->document_type)
            ->set('file', $pdf)
            ->call('update');

        $document->refresh();
        expect($document->hasMedia('file'))->toBeTrue();
    });

    it('can remove existing file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Add existing file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $document->addMedia($tempFile)
            ->usingName($document->title)
            ->usingFileName('existing.pdf')
            ->toMediaCollection('file');

        expect($document->hasMedia('file'))->toBeTrue();

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', $document->title)
            ->set('documentType', $document->document_type)
            ->call('removeFile')
            ->assertSet('removeExistingFile', true)
            ->call('update');

        $document->refresh();
        expect($document->hasMedia('file'))->toBeFalse();

        @unlink($tempFile);
    });

    it('replaces existing file when new file is uploaded', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Add existing file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Old PDF content');
        $document->addMedia($tempFile)
            ->usingName($document->title)
            ->usingFileName('old.pdf')
            ->toMediaCollection('file');

        $oldMedia = $document->getFirstMedia('file');
        expect($oldMedia)->not->toBeNull();

        // Upload new file
        $newPdf = UploadedFile::fake()->create('new-document.pdf', 100, 'application/pdf');
        file_put_contents($newPdf->getRealPath(), '%PDF-1.4 New PDF content');

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', $document->title)
            ->set('documentType', $document->document_type)
            ->set('file', $newPdf)
            ->call('update');

        $document->refresh();
        expect($document->hasMedia('file'))->toBeTrue();

        $newMedia = $document->getFirstMedia('file');
        expect($newMedia->file_name)->toBe('new-document.pdf');

        @unlink($tempFile);
    });

    it('dispatches document-updated event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Actualizado')
            ->call('update')
            ->assertDispatched('document-updated');
    });

    it('redirects to show page after update', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $component = Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Actualizado')
            ->call('update');

        $component->assertRedirect(route('admin.documents.show', $document));
    });
});

describe('Admin Documents Edit - Validation', function () {
    it('requires category_id', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('categoryId', null)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['categoryId']);
    });

    it('requires title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', '')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', str_repeat('a', 256))
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('slug', str_repeat('a', 256))
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['slug' => 'slug-one', 'category_id' => $category->id]);
        $document2 = Document::factory()->create(['slug' => 'slug-two', 'category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document1])
            ->set('slug', 'slug-two')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('allows keeping the same slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'slug' => 'original-slug',
            'category_id' => $category->id,
        ]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'New Title')
            ->set('documentType', 'convocatoria')
            ->call('update');

        expect($document->fresh()->slug)->toBe('original-slug');
    });

    it('requires document_type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('documentType', '')
            ->set('title', 'Documento Test')
            ->call('update')
            ->assertHasErrors(['documentType']);
    });

    it('validates document_type is in allowed values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('documentType', 'invalid_type')
            ->set('title', 'Documento Test')
            ->call('update')
            ->assertHasErrors(['documentType']);
    });

    it('validates category_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('categoryId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['categoryId']);
    });

    it('validates program_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('programId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['programId']);
    });

    it('validates academic_year_id exists if provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Edit::class, ['document' => $document])
            ->set('academicYearId', 99999)
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->call('update')
            ->assertHasErrors(['academicYearId']);
    });

    it('validates file is valid file type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $file = UploadedFile::fake()->create('document.exe', 100, 'application/x-msdownload');

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->set('file', $file)
            ->call('update')
            ->assertHasErrors(['file']);
    });

    it('validates file size is within limit', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a file larger than 20MB (20480 KB)
        $pdf = UploadedFile::fake()->create('large.pdf', 21000, 'application/pdf');

        Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Test')
            ->set('documentType', 'convocatoria')
            ->set('file', $pdf)
            ->call('update')
            ->assertHasErrors(['file']);
    });
});

describe('Admin Documents Edit - Slug Generation', function () {
    it('automatically generates slug from title when title changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'title' => 'Documento Original',
            'slug' => 'documento-original',
            'category_id' => $category->id,
        ]);

        $component = Livewire::test(Edit::class, ['document' => $document])
            ->set('title', 'Documento Actualizado');

        // El slug se regenera porque coincide con el slug del título original
        expect($component->get('slug'))->toBe('documento-actualizado');
    });

    it('does not override custom slug when title changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'title' => 'Documento Original',
            'slug' => 'custom-slug',
            'category_id' => $category->id,
        ]);

        $component = Livewire::test(Edit::class, ['document' => $document])
            ->set('slug', 'another-custom-slug')
            ->set('title', 'New Title');

        expect($component->get('slug'))->toBe('another-custom-slug');
    });

    it('updates slug when title changes and slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'title' => 'Documento Original',
            'slug' => 'documento-original',
            'category_id' => $category->id,
        ]);

        $component = Livewire::test(Edit::class, ['document' => $document])
            ->set('slug', '')
            ->set('title', 'New Title');

        expect($component->get('slug'))->toBe('new-title');
    });
});

describe('Admin Documents Edit - Computed Properties', function () {
    it('loads categories for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Categoría A', 'slug' => 'categoria-a']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Categoría B', 'slug' => 'categoria-b']);
        $document = Document::factory()->create(['category_id' => $category1->id]);

        $component = Livewire::test(Edit::class, ['document' => $document]);

        $categories = $component->get('categories');
        expect($categories->pluck('name')->toArray())->toContain('Categoría A')
            ->and($categories->pluck('name')->toArray())->toContain('Categoría B');
    });

    it('loads programs for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $program1 = Program::factory()->create(['name' => 'Programa A']);
        $program2 = Program::factory()->create(['name' => 'Programa B']);
        $document = Document::factory()->create(['category_id' => $category->id]);

        $component = Livewire::test(Edit::class, ['document' => $document]);

        $programs = $component->get('programs');
        expect($programs->pluck('name')->toArray())->toContain('Programa A')
            ->and($programs->pluck('name')->toArray())->toContain('Programa B');
    });

    it('loads academic years for dropdown', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $academicYear1 = AcademicYear::factory()->create(['year' => '2023-2024']);
        $academicYear2 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $document = Document::factory()->create(['category_id' => $category->id]);

        $component = Livewire::test(Edit::class, ['document' => $document]);

        $academicYears = $component->get('academicYears');
        expect($academicYears->pluck('year')->toArray())->toContain('2023-2024')
            ->and($academicYears->pluck('year')->toArray())->toContain('2024-2025');
    });

    it('loads existing file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Add existing file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $document->addMedia($tempFile)
            ->usingName($document->title)
            ->usingFileName('existing.pdf')
            ->toMediaCollection('file');

        $component = Livewire::test(Edit::class, ['document' => $document]);

        $existingFile = $component->get('existingFile');
        expect($existingFile)->not->toBeNull();
        expect($existingFile->file_name)->toBe('existing.pdf');

        @unlink($tempFile);
    });
});
