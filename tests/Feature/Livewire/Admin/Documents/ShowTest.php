<?php

use App\Livewire\Admin\Documents\Show;
use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\MediaConsent;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
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

describe('Admin Documents Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.show', $document))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with DOCUMENTS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.show', $document))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.show', $document))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without DOCUMENTS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $this->get(route('admin.documents.show', $document))
            ->assertForbidden();
    });
});

describe('Admin Documents Show - Display', function () {
    it('displays document details correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Categoría Test']);
        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $creator = User::factory()->create(['name' => 'Creador Test']);

        $document = Document::factory()->create([
            'category_id' => $category->id,
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Documento Test',
            'slug' => 'documento-test',
            'description' => 'Descripción de prueba',
            'document_type' => 'convocatoria',
            'version' => '1.0',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        Livewire::test(Show::class, ['document' => $document])
            ->assertSee('Documento Test')
            ->assertSee('documento-test')
            ->assertSee('Descripción de prueba')
            ->assertSee('Convocatoria')
            ->assertSee('1.0')
            ->assertSee('Categoría Test')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025');
    });

    it('displays file if document has one', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Add file using temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $document->addMedia($tempFile)
            ->usingName($document->title)
            ->usingFileName('document.pdf')
            ->toMediaCollection('file');

        Livewire::test(Show::class, ['document' => $document])
            ->assertSee('document.pdf');

        @unlink($tempFile);
    });

    it('displays document without file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Show::class, ['document' => $document])
            ->assertSee($document->title);
    });

    it('displays document type badge with correct color', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'document_type' => 'guia',
        ]);

        $component = Livewire::test(Show::class, ['document' => $document]);
        $instance = $component->instance();
        expect($instance->getDocumentTypeColor('guia'))->toBe('green');
    });

    it('displays document with null optional fields', function () {
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

        Livewire::test(Show::class, ['document' => $document])
            ->assertSee($document->title);
    });

    it('displays media consents if document has them', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a valid media_id if media table exists, otherwise use a placeholder
        $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
            'model_type' => 'App\Models\Test',
            'model_id' => 1,
            'collection_name' => 'test',
            'name' => 'test',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]) : 999999;

        // Create media consent
        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        Livewire::test(Show::class, ['document' => $document])
            ->assertSee($document->title);
    });
});

describe('Admin Documents Show - Actions', function () {
    it('can delete a document (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Show::class, ['document' => $document])
            ->call('delete')
            ->assertDispatched('document-deleted')
            ->assertRedirect(route('admin.documents.index'));

        expect($document->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete document with media consents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a valid media_id if media table exists, otherwise use a placeholder
        $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
            'model_type' => 'App\Models\Test',
            'model_id' => 1,
            'collection_name' => 'test',
            'name' => 'test',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]) : 999999;

        // Create media consent BEFORE mounting the component
        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        // Reload document - mount() will load the count automatically
        $document->refresh();

        $component = Livewire::test(Show::class, ['document' => $document]);
        
        // Verify the count is loaded
        expect($component->instance()->document->media_consents_count)->toBeGreaterThan(0);
        
        $component->call('delete')
            ->assertDispatched('document-delete-error');

        expect($document->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted document', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);
        $document->delete();

        // Reload document to get trashed state
        $document = Document::withTrashed()->find($document->id);

        Livewire::test(Show::class, ['document' => $document])
            ->call('restore')
            ->assertDispatched('document-restored');

        expect($document->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a document (only if no relations)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);
        $document->delete();

        // Reload document to get trashed state
        $document = Document::withTrashed()->find($document->id);

        Livewire::test(Show::class, ['document' => $document])
            ->call('forceDelete')
            ->assertDispatched('document-force-deleted')
            ->assertRedirect(route('admin.documents.index'));

        expect(Document::withTrashed()->find($document->id))->toBeNull();
    });

    it('cannot force delete document with media consents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a valid media_id if media table exists, otherwise use a placeholder
        $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
            'model_type' => 'App\Models\Test',
            'model_id' => 1,
            'collection_name' => 'test',
            'name' => 'test',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]) : 999999;

        // Create media consent BEFORE deleting and mounting
        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        $document->delete();

        // Reload document to get trashed state - mount() will load the count
        $document = Document::withTrashed()->find($document->id);

        Livewire::test(Show::class, ['document' => $document])
            ->call('forceDelete')
            ->assertDispatched('document-force-delete-error');

        expect(Document::withTrashed()->find($document->id))->not->toBeNull();
    });
});

describe('Admin Documents Show - Computed Properties', function () {
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

        $component = Livewire::test(Show::class, ['document' => $document]);

        $existingFile = $component->get('existingFile');
        expect($existingFile)->not->toBeNull();
        expect($existingFile->file_name)->toBe('existing.pdf');

        @unlink($tempFile);
    });

    it('returns null for existing file when document has no file', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        $component = Livewire::test(Show::class, ['document' => $document]);

        $existingFile = $component->get('existingFile');
        expect($existingFile)->toBeNull();
    });

    it('checks hasRelationships correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Initially no relationships
        $component = Livewire::test(Show::class, ['document' => $document]);
        expect($component->get('hasRelationships'))->toBeFalse();

        // Create a valid media_id if media table exists, otherwise use a placeholder
        $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
            'model_type' => 'App\Models\Test',
            'model_id' => 1,
            'collection_name' => 'test',
            'name' => 'test',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]) : 999999;

        // Create media consent
        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        // Reload document to get the count
        $document->refresh();
        $document->loadCount('mediaConsents');

        $component = Livewire::test(Show::class, ['document' => $document]);
        expect($component->get('hasRelationships'))->toBeTrue();
    });

    it('checks canDelete correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Initially can delete (no relationships)
        $component = Livewire::test(Show::class, ['document' => $document]);
        $instance = $component->instance();
        expect($instance->canDelete())->toBeTrue();

        // Create a valid media_id if media table exists, otherwise use a placeholder
        $mediaId = Schema::hasTable('media') ? \DB::table('media')->insertGetId([
            'model_type' => 'App\Models\Test',
            'model_id' => 1,
            'collection_name' => 'test',
            'name' => 'test',
            'file_name' => 'test.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => 1024,
            'manipulations' => '[]',
            'custom_properties' => '[]',
            'generated_conversions' => '[]',
            'responsive_images' => '[]',
            'created_at' => now(),
            'updated_at' => now(),
        ]) : 999999;

        // Create media consent
        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        // Reload document to get the count
        $document->refresh();
        $document->loadCount('mediaConsents');

        $component = Livewire::test(Show::class, ['document' => $document]);
        $instance = $component->instance();
        expect($instance->canDelete())->toBeFalse();
    });
});

describe('Admin Documents Show - Helper Methods', function () {
    it('returns correct document type label', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'document_type' => 'convocatoria',
        ]);

        $component = Livewire::test(Show::class, ['document' => $document]);
        $options = $component->instance()->getDocumentTypeOptions();

        expect($options)->toHaveKey('convocatoria');
        expect($options['convocatoria'])->toBe(__('Convocatoria'));
    });

    it('returns correct document type color', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create([
            'category_id' => $category->id,
            'document_type' => 'seguro',
        ]);

        $component = Livewire::test(Show::class, ['document' => $document]);
        $instance = $component->instance();

        expect($instance->getDocumentTypeColor('convocatoria'))->toBe('blue');
        expect($instance->getDocumentTypeColor('modelo'))->toBe('purple');
        expect($instance->getDocumentTypeColor('seguro'))->toBe('orange');
        expect($instance->getDocumentTypeColor('consentimiento'))->toBe('yellow');
        expect($instance->getDocumentTypeColor('guia'))->toBe('green');
        expect($instance->getDocumentTypeColor('faq'))->toBe('cyan');
        expect($instance->getDocumentTypeColor('otro'))->toBe('gray');
        expect($instance->getDocumentTypeColor('unknown'))->toBe('gray');
    });

    it('checks hasFile correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Initially no file
        expect($document->hasMedia('file'))->toBeFalse();

        // Add file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_document_');
        file_put_contents($tempFile, '%PDF-1.4 Test PDF content');
        $document->addMedia($tempFile)
            ->usingName($document->title)
            ->usingFileName('test.pdf')
            ->toMediaCollection('file');

        $document->refresh();
        expect($document->hasMedia('file'))->toBeTrue();

        @unlink($tempFile);
    });
});
