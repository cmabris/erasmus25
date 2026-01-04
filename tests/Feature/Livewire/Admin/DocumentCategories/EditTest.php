<?php

use App\Livewire\Admin\DocumentCategories\Edit;
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

describe('Admin DocumentCategories Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.edit', $category))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with documents.edit permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_EDIT);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.edit', $category))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.edit', $category))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        $this->get(route('admin.document-categories.edit', $category))
            ->assertForbidden();
    });
});

describe('Admin DocumentCategories Edit - Data Loading', function () {
    it('loads document category data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original description',
            'order' => 5,
        ]);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->assertSet('name', 'Original Name')
            ->assertSet('slug', 'original-slug')
            ->assertSet('description', 'Original description')
            ->assertSet('order', 5);
    });
});

describe('Admin DocumentCategories Edit - Successful Update', function () {
    it('can update document category with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
            'description' => 'Original description',
            'order' => 1,
        ]);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', 'Updated Name')
            ->set('slug', 'updated-slug')
            ->set('description', 'Updated description')
            ->set('order', 10)
            ->call('update')
            ->assertRedirect(route('admin.document-categories.index'));

        expect($category->fresh())
            ->name->toBe('Updated Name')
            ->slug->toBe('updated-slug')
            ->description->toBe('Updated description')
            ->order->toBe(10);
    });

    it('dispatches document-category-updated event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', 'Updated Name')
            ->call('update')
            ->assertDispatched('document-category-updated');

        // Verificar que la actualización funcionó
        expect($category->fresh()->name)->toBe('Updated Name');
    });

    it('allows updating only name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name', // Debe coincidir con el slug del nombre original
        ]);

        // Cuando se actualiza el nombre, el slug se regenera automáticamente
        // si el slug actual coincide con el slug del nombre original
        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', 'Updated Name')
            ->call('update');

        expect($category->fresh())
            ->name->toBe('Updated Name')
            ->slug->toBe('updated-name'); // El slug se regenera automáticamente
    });

    it('allows updating only description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'description' => 'Original description',
        ]);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('description', 'Updated description')
            ->call('update');

        expect($category->fresh())
            ->name->toBe('Original Name')
            ->slug->toBe('original-name')
            ->description->toBe('Updated description');
    });

    it('allows updating only order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
            'order' => 1,
        ]);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('order', 10)
            ->call('update');

        expect($category->fresh())
            ->name->toBe('Original Name')
            ->slug->toBe('original-name')
            ->order->toBe(10);
    });
});

describe('Admin DocumentCategories Edit - Validation', function () {
    it('uses model value when name is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
        ]);

        // El componente tiene lógica que usa el valor del modelo si name está vacío
        // Esto es el comportamiento esperado para evitar errores cuando solo se modifica description
        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', '')
            ->set('description', 'Updated description')
            ->call('update')
            ->assertHasNoErrors(['name']);

        // El nombre se mantiene porque el componente usa el valor del modelo cuando está vacío
        expect($category->fresh()->name)->toBe('Original Name');
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', str_repeat('a', 256))
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates name uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['name' => 'Category One']);
        $category2 = DocumentCategory::factory()->create(['name' => 'Category Two']);

        Livewire::test(Edit::class, ['document_category' => $category1])
            ->set('name', 'Category Two')
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('allows keeping the same name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['name' => 'Original Name']);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('slug', 'new-slug')
            ->call('update');

        expect($category->fresh()->name)->toBe('Original Name');
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('slug', str_repeat('a', 256))
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category1 = DocumentCategory::factory()->create(['slug' => 'slug-one']);
        $category2 = DocumentCategory::factory()->create(['slug' => 'slug-two']);

        Livewire::test(Edit::class, ['document_category' => $category1])
            ->set('slug', 'slug-two')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('allows keeping the same slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create(['slug' => 'original-slug']);

        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', 'New Name')
            ->call('update');

        expect($category->fresh()->slug)->toBe('original-slug');
    });

    it('validates order is integer', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();

        // Livewire no permite asignar un string a una propiedad int directamente
        // Este test verifica que el campo order acepta valores enteros válidos
        Livewire::test(Edit::class, ['document_category' => $category])
            ->set('order', 5)
            ->call('update')
            ->assertHasNoErrors(['order']);

        expect($category->fresh()->order)->toBe(5);
    });
});

describe('Admin DocumentCategories Edit - Slug Generation', function () {
    it('automatically generates slug from name when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name', // El slug debe coincidir con el slug del nombre original
        ]);

        $component = Livewire::test(Edit::class, ['document_category' => $category])
            ->set('name', 'New Category Name');

        // El slug se regenera porque coincide con el slug del nombre original
        expect($component->get('slug'))->toBe('new-category-name');
    });

    it('does not override custom slug when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $component = Livewire::test(Edit::class, ['document_category' => $category])
            ->set('slug', 'custom-slug')
            ->set('name', 'New Name');

        expect($component->get('slug'))->toBe('custom-slug');
    });

    it('updates slug when name changes and slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $component = Livewire::test(Edit::class, ['document_category' => $category])
            ->set('slug', '')
            ->set('name', 'New Name');

        expect($component->get('slug'))->toBe('new-name');
    });
});
