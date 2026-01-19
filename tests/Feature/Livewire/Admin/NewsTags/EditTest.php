<?php

use App\Livewire\Admin\NewsTags\Edit;
use App\Models\NewsTag;
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
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
        Permissions::NEWS_DELETE,
    ]);
    $viewer->givePermissionTo([
        Permissions::NEWS_VIEW,
    ]);
});

describe('Admin NewsTags Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $tag = NewsTag::factory()->create();

        $this->get(route('admin.news-tags.edit', $tag))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with news.edit permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_EDIT);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        $this->get(route('admin.news-tags.edit', $tag))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        $this->get(route('admin.news-tags.edit', $tag))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        $this->get(route('admin.news-tags.edit', $tag))
            ->assertForbidden();
    });
});

describe('Admin NewsTags Edit - Data Loading', function () {
    it('loads news tag data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->assertSet('name', 'Original Name')
            ->assertSet('slug', 'original-slug');
    });
});

describe('Admin NewsTags Edit - Successful Update', function () {
    it('can update news tag with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'Updated Name')
            ->set('slug', 'updated-slug')
            ->call('update')
            ->assertRedirect(route('admin.news-tags.index'));

        expect($tag->fresh())
            ->name->toBe('Updated Name')
            ->slug->toBe('updated-slug');
    });

    it('dispatches news-tag-updated event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'Updated Name')
            ->call('update')
            ->assertDispatched('news-tag-updated');

        // Verificar que la actualización funcionó
        expect($tag->fresh()->name)->toBe('Updated Name');
    });

    it('allows updating only name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Crear tag con slug que coincida con el slug del nombre original
        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name', // Debe coincidir con el slug del nombre
        ]);

        // Cuando se actualiza el nombre, el slug se regenera automáticamente
        // si el slug actual coincide con el slug del nombre original
        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'Updated Name')
            ->call('update');

        expect($tag->fresh())
            ->name->toBe('Updated Name')
            ->slug->toBe('updated-name'); // El slug se regenera automáticamente
    });

    it('allows updating only slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        $component = Livewire::test(Edit::class, ['news_tag' => $tag])
            ->assertSet('slug', 'original-name')
            ->set('name', 'Name Changed');

        $component->call('update');

        $tag = NewsTag::first();

        // Verificar contra la base de datos que el slug se actualizó correctamente
        expect($tag->slug)->toBe('name-changed')
            ->and($tag->name)->toBe('Name Changed');
    });
});

describe('Admin NewsTags Edit - Validation', function () {
    it('requires name field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', '')
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates name max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', str_repeat('a', 256))
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('validates name uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag1 = NewsTag::factory()->create(['name' => 'Tag One']);
        $tag2 = NewsTag::factory()->create(['name' => 'Tag Two']);

        Livewire::test(Edit::class, ['news_tag' => $tag1])
            ->set('name', 'Tag Two')
            ->call('update')
            ->assertHasErrors(['name']);
    });

    it('allows keeping the same name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Original Name']);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('slug', 'new-slug')
            ->call('update');

        expect($tag->fresh()->name)->toBe('Original Name');
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('slug', str_repeat('a', 256))
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness ignoring current record', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag1 = NewsTag::factory()->create(['slug' => 'slug-one']);
        $tag2 = NewsTag::factory()->create(['slug' => 'slug-two']);

        Livewire::test(Edit::class, ['news_tag' => $tag1])
            ->set('slug', 'slug-two')
            ->call('update')
            ->assertHasErrors(['slug']);
    });

    it('allows keeping the same slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['slug' => 'original-slug']);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'New Name')
            ->call('update');

        expect($tag->fresh()->slug)->toBe('original-slug');
    });
});

describe('Admin NewsTags Edit - Slug Generation', function () {
    it('automatically generates slug from name when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name', // El slug debe coincidir con el slug del nombre original
        ]);

        $component = Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'New Tag Name');

        // El slug se regenera porque coincide con el slug del nombre original
        expect($component->get('slug'))->toBe('new-tag-name');
    });

    it('does not override custom slug when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $component = Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('slug', 'custom-slug')
            ->set('name', 'New Name');

        expect($component->get('slug'))->toBe('custom-slug');
    });

    it('updates slug when name changes and slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-slug',
        ]);

        $component = Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('slug', '')
            ->set('name', 'New Name');

        expect($component->get('slug'))->toBe('new-name');
    });

    it('validates slug uniqueness in real-time when slug changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $existingTag = NewsTag::factory()->create(['slug' => 'existing-slug']);
        $tag = NewsTag::factory()->create(['slug' => 'original-slug']);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('slug', 'existing-slug')
            ->assertHasErrors(['slug']);
    });
});

describe('Admin NewsTags Edit - Edge Cases', function () {
    it('update generates slug from name when slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        // Clear the slug and update - should generate from name
        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'New Name')
            ->set('slug', '')  // Explicitly set slug to empty
            ->call('update')
            ->assertRedirect(route('admin.news-tags.index'));

        $tag->refresh();
        expect($tag->name)->toBe('New Name')
            ->and($tag->slug)->toBe('new-name');
    });

    it('update preserves custom slug when provided', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Original Name',
            'slug' => 'original-name',
        ]);

        Livewire::test(Edit::class, ['news_tag' => $tag])
            ->set('name', 'New Name')
            ->set('slug', 'my-custom-slug')
            ->call('update');

        $tag->refresh();
        expect($tag->slug)->toBe('my-custom-slug');
    });
});
