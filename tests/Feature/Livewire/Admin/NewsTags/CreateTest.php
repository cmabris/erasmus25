<?php

use App\Livewire\Admin\NewsTags\Create;
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

describe('Admin NewsTags Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.news-tags.create'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with news.create permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_CREATE);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.create'))
            ->assertForbidden();
    });
});

describe('Admin NewsTags Create - Successful Creation', function () {
    it('can create a news tag with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Movilidad')
            ->set('slug', 'movilidad')
            ->call('store')
            ->assertRedirect(route('admin.news-tags.index'));

        expect(NewsTag::where('name', 'Movilidad')->exists())->toBeTrue();
    });

    it('creates news tag with automatically generated slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Experiencias Internacionales')
            ->call('store');

        $tag = NewsTag::where('name', 'Experiencias Internacionales')->first();
        expect($tag->slug)->toBe('experiencias-internacionales');
    });

    it('allows custom slug to override auto-generated slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Movilidad')
            ->set('slug', 'custom-slug')
            ->call('store');

        $tag = NewsTag::where('name', 'Movilidad')->first();
        expect($tag->slug)->toBe('custom-slug');
    });

    it('dispatches news-tag-created event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Test Tag')
            ->call('store')
            ->assertDispatched('news-tag-created');
    });
});

describe('Admin NewsTags Create - Validation', function () {
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

        NewsTag::factory()->create(['name' => 'Existing Tag']);

        Livewire::test(Create::class)
            ->set('name', 'Existing Tag')
            ->call('store')
            ->assertHasErrors(['name']);
    });

    it('validates slug max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->set('name', 'Test Tag')
            ->set('slug', str_repeat('a', 256))
            ->call('store')
            ->assertHasErrors(['slug']);
    });

    it('validates slug uniqueness', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(Create::class)
            ->set('name', 'New Tag')
            ->set('slug', 'existing-slug')
            ->call('store')
            ->assertHasErrors(['slug']);
    });
});

describe('Admin NewsTags Create - Slug Generation', function () {
    it('automatically generates slug from name when name changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Create::class)
            ->set('name', 'Test Tag Name');

        expect($component->get('slug'))->toBe('test-tag-name');
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

    it('validates slug uniqueness in real-time when slug changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['slug' => 'existing-slug']);

        Livewire::test(Create::class)
            ->set('slug', 'existing-slug')
            ->assertHasErrors(['slug']);
    });
});

describe('Admin NewsTags Create - Edge Cases', function () {
    it('store generates slug from name when slug is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Directamente setear nombre sin slug (simular el caso donde el slug es vacío en validated)
        Livewire::test(Create::class)
            ->set('name', 'Tag Without Slug')
            ->set('slug', '')  // Explicitly set slug to empty
            ->call('store')
            ->assertRedirect(route('admin.news-tags.index'));

        $tag = NewsTag::where('name', 'Tag Without Slug')->first();
        expect($tag)->not->toBeNull()
            ->and($tag->slug)->toBe('tag-without-slug');
    });
});
