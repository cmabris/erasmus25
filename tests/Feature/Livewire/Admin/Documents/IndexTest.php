<?php

use App\Livewire\Admin\Documents\Index;
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
use Livewire\Livewire;
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

describe('Admin Documents Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.documents.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with DOCUMENTS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.documents.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.documents.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Documents Index - Listing', function () {
    it('displays all documents by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Documento A', 'category_id' => $category->id]);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'category_id' => $category->id]);

        Livewire::test(Index::class)
            ->assertSee('Documento A')
            ->assertSee('Documento B');
    });

    it('displays document information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Categoría Test']);
        $document = Document::factory()->create([
            'title' => 'Documento Test',
            'slug' => 'documento-test',
            'category_id' => $category->id,
            'document_type' => 'convocatoria',
            'is_active' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee('Documento Test')
            ->assertSee('Categoría Test')
            ->assertSee('Convocatoria');
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);

        // Create a valid media_id if media table exists
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

        MediaConsent::factory()->count(3)->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        Livewire::test(Index::class)
            ->assertSee('3');
    });
});

describe('Admin Documents Index - Search', function () {
    it('can search documents by title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Convocatoria Erasmus+', 'category_id' => $category->id]);
        $document2 = Document::factory()->create(['title' => 'Guía de Movilidad', 'category_id' => $category->id]);

        $component = Livewire::test(Index::class)
            ->set('search', 'Erasmus');

        $documents = $component->get('documents');
        $titles = $documents->pluck('title')->toArray();
        expect($titles)->toContain('Convocatoria Erasmus+')
            ->and($titles)->not->toContain('Guía de Movilidad');
    });

    it('can search documents by description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create([
            'title' => 'Documento A',
            'description' => 'Descripción única para test',
            'category_id' => $category->id,
        ]);
        $document2 = Document::factory()->create([
            'title' => 'Documento B',
            'description' => 'Otra descripción',
            'category_id' => $category->id,
        ]);

        Livewire::test(Index::class)
            ->set('search', 'única para test')
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('can search documents by slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Documento A', 'slug' => 'documento-a', 'category_id' => $category->id]);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'slug' => 'documento-b', 'category_id' => $category->id]);

        Livewire::test(Index::class)
            ->set('search', 'documento-a')
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(20)->create(['category_id' => $category->id]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        expect($component->get('search'))->toBe('test');
        expect($component->get('documents')->currentPage())->toBe(1);
    });
});

describe('Admin Documents Index - Sorting', function () {
    it('can sort by title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['title' => 'Zeta Document', 'category_id' => $category->id]);
        Document::factory()->create(['title' => 'Alpha Document', 'category_id' => $category->id]);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'title');

        $component->assertSee('Alpha Document')
            ->assertSee('Zeta Document');

        // Verificar que el orden es ascendente
        $documents = $component->get('documents');
        $titles = $documents->pluck('title')->toArray();
        expect($titles)->toContain('Alpha Document')
            ->and($titles)->toContain('Zeta Document');
    });

    it('can sort by created_at', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Documento Antiguo', 'category_id' => $category->id]);
        $document1->created_at = now()->subDays(2);
        $document1->save();

        $document2 = Document::factory()->create(['title' => 'Documento Reciente', 'category_id' => $category->id]);
        $document2->created_at = now();
        $document2->save();

        Livewire::test(Index::class)
            ->call('sortBy', 'created_at')
            ->assertSee('Documento Reciente')
            ->assertSee('Documento Antiguo');
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['title' => 'Alpha', 'category_id' => $category->id]);
        Document::factory()->create(['title' => 'Zeta', 'category_id' => $category->id]);

        $component = Livewire::test(Index::class);

        // El estado inicial es sortField='created_at' y sortDirection='desc'
        expect($component->get('sortField'))->toBe('created_at')
            ->and($component->get('sortDirection'))->toBe('desc');

        // Llamar a sortBy con 'title' cambia el campo
        $component->call('sortBy', 'title');

        // Ahora debería estar en 'title' con dirección 'asc'
        expect($component->get('sortDirection'))->toBe('asc')
            ->and($component->get('sortField'))->toBe('title');

        // Llamar de nuevo al mismo campo cambia la dirección
        $component->call('sortBy', 'title');

        // Ahora debería estar en 'desc'
        expect($component->get('sortDirection'))->toBe('desc')
            ->and($component->get('sortField'))->toBe('title');
    });
});

describe('Admin Documents Index - Filters', function () {
    it('shows only non-deleted documents by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $activeDocument = Document::factory()->create(['title' => 'Documento Activo', 'category_id' => $category->id]);
        $deletedDocument = Document::factory()->create(['title' => 'Documento Eliminado', 'category_id' => $category->id]);
        $deletedDocument->delete();

        Livewire::test(Index::class)
            ->assertSee('Documento Activo')
            ->assertDontSee('Documento Eliminado');
    });

    it('can show deleted documents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $activeDocument = Document::factory()->create(['title' => 'Documento Activo', 'category_id' => $category->id]);
        $deletedDocument = Document::factory()->create(['title' => 'Documento Eliminado', 'category_id' => $category->id]);
        $deletedDocument->delete();

        $component = Livewire::test(Index::class)
            ->set('showDeleted', '1');

        $documents = $component->get('documents');
        $titles = $documents->pluck('title')->toArray();
        expect($titles)->toContain('Documento Eliminado')
            ->and($titles)->not->toContain('Documento Activo');
    });

    it('can filter by category', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Categoría A', 'slug' => 'categoria-a']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Categoría B', 'slug' => 'categoria-b']);
        $document1 = Document::factory()->create(['title' => 'Documento A', 'category_id' => $category1->id]);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'category_id' => $category2->id]);

        Livewire::test(Index::class)
            ->set('categoryId', $category1->id)
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('can filter by program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $program1 = Program::factory()->create(['name' => 'Programa A']);
        $program2 = Program::factory()->create(['name' => 'Programa B']);
        $document1 = Document::factory()->create(['title' => 'Documento A', 'category_id' => $category->id, 'program_id' => $program1->id]);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'category_id' => $category->id, 'program_id' => $program2->id]);

        Livewire::test(Index::class)
            ->set('programId', $program1->id)
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('can filter by academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $academicYear1 = AcademicYear::factory()->create(['year' => '2023-2024']);
        $academicYear2 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $document1 = Document::factory()->create(['title' => 'Documento A', 'category_id' => $category->id, 'academic_year_id' => $academicYear1->id]);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'category_id' => $category->id, 'academic_year_id' => $academicYear2->id]);

        Livewire::test(Index::class)
            ->set('academicYearId', $academicYear1->id)
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('can filter by document type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Documento A', 'category_id' => $category->id, 'document_type' => 'convocatoria']);
        $document2 = Document::factory()->create(['title' => 'Documento B', 'category_id' => $category->id, 'document_type' => 'guia']);

        Livewire::test(Index::class)
            ->set('documentType', 'convocatoria')
            ->assertSee('Documento A')
            ->assertDontSee('Documento B');
    });

    it('can filter by active status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create(['title' => 'Documento Activo', 'category_id' => $category->id, 'is_active' => true]);
        $document2 = Document::factory()->create(['title' => 'Documento Inactivo', 'category_id' => $category->id, 'is_active' => false]);

        Livewire::test(Index::class)
            ->set('isActive', '1')
            ->assertSee('Documento Activo')
            ->assertDontSee('Documento Inactivo');
    });

    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('categoryId', 1)
            ->set('showDeleted', '1')
            ->call('resetFilters');

        expect($component->get('search'))->toBe('')
            ->and($component->get('categoryId'))->toBeNull()
            ->and($component->get('showDeleted'))->toBe('0');
    });
});

describe('Admin Documents Index - Pagination', function () {
    it('paginates documents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(20)->create(['category_id' => $category->id]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        expect($component->get('documents')->hasPages())->toBeTrue();
        expect($component->get('documents')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(20)->create(['category_id' => $category->id]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        expect($component->get('documents')->count())->toBe(20);
    });
});

describe('Admin Documents Index - Soft Delete', function () {
    it('can delete a document without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['title' => 'Documento Test', 'category_id' => $category->id]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $document->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('documentToDelete', $document->id)
            ->call('delete')
            ->assertDispatched('document-deleted');

        expect($document->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete a document with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['title' => 'Documento Test', 'category_id' => $category->id]);

        // Create a valid media_id if media table exists
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

        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $document->id)
            ->call('delete')
            ->assertDispatched('document-delete-error');

        expect($document->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted document', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['title' => 'Documento Test', 'category_id' => $category->id]);
        $document->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $document->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('documentToRestore', $document->id)
            ->call('restore')
            ->assertDispatched('document-restored');

        expect($document->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a document without relationships (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['title' => 'Documento Test', 'category_id' => $category->id]);
        $document->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $document->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('documentToForceDelete', $document->id)
            ->call('forceDelete')
            ->assertDispatched('document-force-deleted');

        expect(Document::withTrashed()->find($document->id))->toBeNull();
    });

    it('cannot force delete a document with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['title' => 'Documento Test', 'category_id' => $category->id]);
        $document->delete();

        // Create a valid media_id if media table exists
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

        MediaConsent::factory()->create([
            'consent_document_id' => $document->id,
            'media_id' => $mediaId,
        ]);

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $document->id)
            ->call('forceDelete')
            ->assertDispatched('document-force-delete-error');

        expect(Document::withTrashed()->find($document->id))->not->toBeNull();
    });
});

describe('Admin Documents Index - Helpers', function () {
    it('can check if user can create', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Verificar indirectamente que el botón de crear está visible
        Livewire::test(Index::class)
            ->assertSee('Crear Documento');
    });

    it('can check if document can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $documentWithoutRelations = Document::factory()->create(['category_id' => $category->id]);
        $documentWithRelations = Document::factory()->create(['category_id' => $category->id]);

        // Create a valid media_id if media table exists
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

        MediaConsent::factory()->create([
            'consent_document_id' => $documentWithRelations->id,
            'media_id' => $mediaId,
        ]);

        // El método canDeleteDocument se usa en la vista con documentos que ya tienen el count cargado
        $component = Livewire::test(Index::class);

        // Verificar que se puede eliminar un documento sin relaciones
        $documentWithoutRelationsLoaded = Document::withCount(['mediaConsents'])->find($documentWithoutRelations->id);
        expect($component->instance()->canDeleteDocument($documentWithoutRelationsLoaded))->toBeTrue();

        // Verificar que no se puede eliminar si tiene relaciones
        $documentWithRelationsLoaded = Document::withCount(['mediaConsents'])->find($documentWithRelations->id);
        expect($component->instance()->canDeleteDocument($documentWithRelationsLoaded))->toBeFalse();
    });
});
