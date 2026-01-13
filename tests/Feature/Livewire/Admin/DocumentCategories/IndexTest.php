<?php

use App\Livewire\Admin\DocumentCategories\Index;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Limpiar caché de traducciones para evitar interferencias entre tests
    Cache::forget('translations.active_languages');
    Cache::forget('translations.active_programs');
    Cache::forget('translations.all_settings');

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
    $editor->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
    ]);
});

describe('Admin DocumentCategories Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.document-categories.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with documents.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin DocumentCategories Index - Listing', function () {
    it('displays all document categories by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Convocatorias']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Modelos']);

        Livewire::test(Index::class)
            ->assertSee('Convocatorias')
            ->assertSee('Modelos');
    });

    it('displays document category information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Convocatorias',
            'slug' => 'convocatorias',
            'description' => 'Documentos relacionados con convocatorias',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Convocatorias')
            ->assertSee('convocatorias')
            ->assertSee('Documentos relacionados con convocatorias');
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(5)->create(['category_id' => $category->id]);

        Livewire::test(Index::class)
            ->assertSee('5');
    });
});

describe('Admin DocumentCategories Index - Search', function () {
    it('can search document categories by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Clear any existing categories to avoid interference from other tests
        DocumentCategory::query()->delete();

        $category1 = DocumentCategory::factory()->create(['name' => 'Convocatorias']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Modelos']);

        $component = Livewire::test(Index::class)
            ->set('search', 'Convocatorias');

        $categories = $component->get('documentCategories');
        $categoryNames = $categories->pluck('name')->toArray();
        expect($categoryNames)->toContain('Convocatorias')
            ->and($categoryNames)->not->toContain('Modelos');
    });

    it('can search document categories by slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Convocatorias', 'slug' => 'convocatorias']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Modelos', 'slug' => 'modelos']);

        Livewire::test(Index::class)
            ->set('search', 'convocatorias')
            ->assertSee('Convocatorias')
            ->assertDontSee('Modelos');
    });

    it('can search document categories by description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Clear any existing categories to avoid interference from other tests
        DocumentCategory::query()->delete();

        $category1 = DocumentCategory::factory()->create([
            'name' => 'Convocatorias',
            'description' => 'Documentos de convocatorias',
        ]);
        $category2 = DocumentCategory::factory()->create([
            'name' => 'Modelos',
            'description' => 'Documentos modelo',
        ]);

        $component = Livewire::test(Index::class)
            ->set('search', 'convocatorias');

        $categories = $component->get('documentCategories');
        $categoryNames = $categories->pluck('name')->toArray();

        expect($categoryNames)->toContain('Convocatorias')
            ->and($categoryNames)->not->toContain('Modelos');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        expect($component->get('search'))->toBe('test');
        expect($component->get('documentCategories')->currentPage())->toBe(1);
    });
});

describe('Admin DocumentCategories Index - Sorting', function () {
    it('can sort by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->create(['name' => 'Zeta']);
        DocumentCategory::factory()->create(['name' => 'Alpha']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'name');

        $component->assertSee('Alpha')
            ->assertSee('Zeta');

        $categories = $component->get('documentCategories');
        $names = $categories->pluck('name')->toArray();
        expect($names)->toContain('Alpha')
            ->and($names)->toContain('Zeta');
    });

    it('can sort by order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->create(['name' => 'Category Z', 'order' => 10]);
        DocumentCategory::factory()->create(['name' => 'Category A', 'order' => 1]);

        Livewire::test(Index::class)
            ->call('sortBy', 'order')
            ->assertSee('Category A')
            ->assertSee('Category Z');
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->create(['name' => 'Alpha']);
        DocumentCategory::factory()->create(['name' => 'Zeta']);

        $component = Livewire::test(Index::class);

        // El estado inicial es sortField='order' y sortDirection='asc'
        expect($component->get('sortField'))->toBe('order')
            ->and($component->get('sortDirection'))->toBe('asc');

        // Llamar a sortBy con 'name' cambia el campo
        $component->call('sortBy', 'name');

        expect($component->get('sortDirection'))->toBe('asc')
            ->and($component->get('sortField'))->toBe('name');

        // Llamar de nuevo al mismo campo cambia la dirección
        $component->call('sortBy', 'name');

        expect($component->get('sortDirection'))->toBe('desc')
            ->and($component->get('sortField'))->toBe('name');
    });
});

describe('Admin DocumentCategories Index - Filters', function () {
    it('shows only non-deleted document categories by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeCategory = DocumentCategory::factory()->create(['name' => 'Active Category']);
        $deletedCategory = DocumentCategory::factory()->create(['name' => 'Deleted Category']);
        $deletedCategory->delete();

        Livewire::test(Index::class)
            ->assertSee('Active Category')
            ->assertDontSee('Deleted Category');
    });

    it('can show deleted document categories', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeCategory = DocumentCategory::factory()->create(['name' => 'Active Category']);
        $deletedCategory = DocumentCategory::factory()->create(['name' => 'Deleted Category']);
        $deletedCategory->delete();

        $component = Livewire::test(Index::class)
            ->set('showDeleted', '1');

        $categories = $component->get('documentCategories');
        $categoryNames = $categories->pluck('name')->toArray();
        expect($categoryNames)->toContain('Deleted Category')
            ->and($categoryNames)->not->toContain('Active Category');
    });
});

describe('Admin DocumentCategories Index - Pagination', function () {
    it('paginates document categories', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        expect($component->get('documentCategories')->hasPages())->toBeTrue();
        expect($component->get('documentCategories')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        expect($component->get('documentCategories')->count())->toBe(20);
    });
});

describe('Admin DocumentCategories Index - Soft Delete', function () {
    it('can delete a document category without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Test Category']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $category->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('documentCategoryToDelete', $category->id)
            ->call('delete')
            ->assertDispatched('document-category-deleted');

        expect($category->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete a document category with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Test Category']);
        Document::factory()->create(['category_id' => $category->id]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $category->id)
            ->call('delete')
            ->assertDispatched('document-category-delete-error');

        expect($category->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted document category', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Test Category']);
        $category->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $category->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('documentCategoryToRestore', $category->id)
            ->call('restore')
            ->assertDispatched('document-category-restored');

        expect($category->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a document category without relationships (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Test Category']);
        $category->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $category->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('documentCategoryToForceDelete', $category->id)
            ->call('forceDelete')
            ->assertDispatched('document-category-force-deleted');

        expect(DocumentCategory::withTrashed()->find($category->id))->toBeNull();
    });

    it('cannot force delete a document category with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Test Category']);
        Document::factory()->create(['category_id' => $category->id]);
        $category->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $category->id)
            ->call('forceDelete')
            ->assertDispatched('document-category-force-delete-error');

        expect(DocumentCategory::withTrashed()->find($category->id))->not->toBeNull();
    });
});

describe('Admin DocumentCategories Index - Helpers', function () {
    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('showDeleted', '1')
            ->call('resetFilters');

        expect($component->get('search'))->toBe('')
            ->and($component->get('showDeleted'))->toBe('0');
    });

    it('can check if user can create', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Verificar indirectamente que el botón de crear está visible
        // (esto indica que canCreate() devuelve true)
        Livewire::test(Index::class)
            ->assertSee('Crear Categoría');
    });

    it('can check if document category can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $categoryWithoutRelations = DocumentCategory::factory()->create();
        $categoryWithRelations = DocumentCategory::factory()->create();
        Document::factory()->create(['category_id' => $categoryWithRelations->id]);

        // El método canDeleteDocumentCategory se usa en la vista con categories que ya tienen el count cargado
        // Usamos instance() para acceder al método directamente
        $component = Livewire::test(Index::class);

        // Verificar que se puede eliminar una categoría sin relaciones
        $categoryWithoutRelationsLoaded = DocumentCategory::withCount(['documents'])->find($categoryWithoutRelations->id);
        expect($component->instance()->canDeleteDocumentCategory($categoryWithoutRelationsLoaded))->toBeTrue();

        // Verificar que no se puede eliminar si tiene relaciones
        $categoryWithRelationsLoaded = DocumentCategory::withCount(['documents'])->find($categoryWithRelations->id);
        expect($component->instance()->canDeleteDocumentCategory($categoryWithRelationsLoaded))->toBeFalse();
    });
});
