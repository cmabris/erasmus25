<?php

use App\Livewire\Admin\DocumentCategories\Show;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
    $editor->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
        Permissions::DOCUMENTS_CREATE,
        Permissions::DOCUMENTS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::DOCUMENTS_VIEW,
    ]);
});

describe('Admin DocumentCategories Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.show', $category))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with documents.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_VIEW);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.show', $category))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.show', $category))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.show', $category))
            ->assertForbidden();
    });
});

describe('Admin DocumentCategories Show - Display', function () {
    it('displays document category details correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Convocatorias',
            'slug' => 'convocatorias',
            'description' => 'Documentos relacionados con convocatorias',
            'order' => 1,
        ]);

        Livewire::test(Show::class, ['document_category' => $category])
            ->assertSee('Convocatorias')
            ->assertSee('convocatorias')
            ->assertSee('Documentos relacionados con convocatorias')
            ->assertSee('1');
    });

    it('displays statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(5)->create(['category_id' => $category->id]);

        $component = Livewire::test(Show::class, ['document_category' => $category->loadCount('documents')]);
        $statistics = $component->get('statistics');

        expect($statistics['total_documents'])->toBe(5);
    });

    it('displays related documents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $document1 = Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'Documento 1',
        ]);
        $document2 = Document::factory()->create([
            'category_id' => $category->id,
            'title' => 'Documento 2',
        ]);

        Livewire::test(Show::class, ['document_category' => $category->load('documents')])
            ->assertSee('Documento 1')
            ->assertSee('Documento 2');
    });

    it('displays category without description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Test Category',
            'description' => null,
        ]);

        Livewire::test(Show::class, ['document_category' => $category])
            ->assertSee('Test Category');
    });
});

describe('Admin DocumentCategories Show - Actions', function () {
    it('can delete a document category without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Show::class, ['document_category' => $category->loadCount('documents')])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('document-category-deleted')
            ->assertRedirect(route('admin.document-categories.index'));

        expect($category->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete a document category with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['category_id' => $category->id]);

        // El componente recarga la categoría en mount() con loadCount
        // El método delete() también refresca el count antes de verificar
        $component = Livewire::test(Show::class, ['document_category' => $category]);

        // Verificar que canDelete() devuelve false cuando tiene relaciones
        expect($component->instance()->canDelete())->toBeFalse();

        // Intentar eliminar debería disparar el error
        $component
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('document-category-delete-error');

        // Verificar que la categoría no se eliminó
        expect($category->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted document category', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $category->delete();

        Livewire::test(Show::class, ['document_category' => $category->loadCount('documents')])
            ->set('showRestoreModal', true)
            ->call('restore')
            ->assertDispatched('document-category-restored');

        expect($category->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a document category without relationships (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        $category->delete();

        Livewire::test(Show::class, ['document_category' => $category->loadCount('documents')])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('document-category-force-deleted')
            ->assertRedirect(route('admin.document-categories.index'));

        expect(DocumentCategory::withTrashed()->find($category->id))->toBeNull();
    });

    it('cannot force delete a document category with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->create(['category_id' => $category->id]);
        $category->delete();

        // El componente recarga la categoría en mount() con loadCount
        // El método forceDelete() también refresca el count antes de verificar
        $categoryTrashed = DocumentCategory::withTrashed()->find($category->id);
        $component = Livewire::test(Show::class, ['document_category' => $categoryTrashed]);

        // Verificar que hasRelationships devuelve true cuando tiene relaciones
        expect($component->get('hasRelationships'))->toBeTrue();

        // Intentar eliminar permanentemente debería disparar el error
        $component
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('document-category-force-delete-error');

        // Verificar que la categoría no se eliminó permanentemente
        expect(DocumentCategory::withTrashed()->find($category->id))->not->toBeNull();
    });
});

describe('Admin DocumentCategories Show - Helpers', function () {
    it('can check if document category can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $categoryWithoutRelations = DocumentCategory::factory()->create();
        $categoryWithRelations = DocumentCategory::factory()->create();
        Document::factory()->create(['category_id' => $categoryWithRelations->id]);

        // Verificar que se puede eliminar una categoría sin relaciones
        $component1 = Livewire::test(Show::class, ['document_category' => $categoryWithoutRelations->loadCount('documents')]);
        expect($component1->instance()->canDelete())->toBeTrue();

        // Verificar que no se puede eliminar si tiene relaciones
        $component2 = Livewire::test(Show::class, ['document_category' => $categoryWithRelations->loadCount('documents')]);
        expect($component2->instance()->canDelete())->toBeFalse();
    });

    it('can check if document category has relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $categoryWithoutRelations = DocumentCategory::factory()->create();
        $categoryWithRelations = DocumentCategory::factory()->create();
        Document::factory()->create(['category_id' => $categoryWithRelations->id]);

        // Verificar que no tiene relaciones
        $component1 = Livewire::test(Show::class, ['document_category' => $categoryWithoutRelations->loadCount('documents')]);
        expect($component1->get('hasRelationships'))->toBeFalse();

        // Verificar que tiene relaciones
        $component2 = Livewire::test(Show::class, ['document_category' => $categoryWithRelations->loadCount('documents')]);
        expect($component2->get('hasRelationships'))->toBeTrue();
    });
});
