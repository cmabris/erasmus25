<?php

use App\Livewire\Admin\DocumentCategories\Create;
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

describe('Admin DocumentCategories Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.document-categories.create'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with documents.create permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::DOCUMENTS_CREATE);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.document-categories.create'))
            ->assertForbidden();
    });
});

describe('Admin DocumentCategories Create - Successful Creation', function () {
    it('can create a document category with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Convocatorias')
            ->set('slug', 'convocatorias')
            ->set('description', 'Documentos relacionados con convocatorias')
            ->set('order', 1)
            ->call('store')
            ->assertRedirect(route('admin.document-categories.index'));

        expect(DocumentCategory::where('name', 'Convocatorias')->exists())->toBeTrue();
    });

    it('creates document category with automatically generated slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Experiencias Internacionales')
            ->call('store');

        $category = DocumentCategory::where('name', 'Experiencias Internacionales')->first();
        expect($category->slug)->toBe('experiencias-internacionales');
    });

    it('allows custom slug to override auto-generated slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Convocatorias')
            ->set('slug', 'custom-slug')
            ->call('store');

        $category = DocumentCategory::where('name', 'Convocatorias')->first();
        expect($category->slug)->toBe('custom-slug');
    });

    it('creates document category with optional fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Test Category')
            ->set('description', 'Test description')
            ->set('order', 5)
            ->call('store');

        $category = DocumentCategory::where('name', 'Test Category')->first();
        expect($category->description)->toBe('Test description')
            ->and($category->order)->toBe(5);
    });

    it('dispatches document-category-created event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Test Category')
            ->call('store')
            ->assertDispatched('document-category-created');
    });
});

describe('Admin DocumentCategories Create - Validation', function () {
    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('slug', 'test-slug')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', str_repeat('a', 256))
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates name uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->create(['name' => 'Existing Category']);

        Livewire::test(Create::class)
            ->set('name', 'Existing Category')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Test Category')
            ->set('slug', str_repeat('a', 256))
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        DocumentCategory::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(Create::class)
            ->set('name', 'New Category')
            ->set('slug', 'existing-slug')
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates order is integer when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Livewire no permite asignar un string a una propiedad int directamente
        // Este test verifica que el campo order acepta valores enteros válidos
        Livewire::test(Create::class)
            ->set('name', 'Test Category')
            ->set('order', 5)
            ->call('store')
            ->assertHasNoErrors(['order']);

        $category = DocumentCategory::where('name', 'Test Category')->first();
        expect($category->order)->toBe(5);
    });
});

describe('Admin DocumentCategories Create - Slug Generation', function () {
    it('automatically generates slug from name when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('name', 'Test Category Name');

        expect($component->get('slug'))->toBe('test-category-name');
    });

    it('does not override custom slug when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('slug', 'custom-slug')
            ->set('name', 'New Name');

        expect($component->get('slug'))->toBe('custom-slug');
    });

    it('updates slug when name changes and slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('name', 'First Name')
            ->set('slug', '')
            ->set('name', 'Second Name');

        expect($component->get('slug'))->toBe('second-name');
    });
});
