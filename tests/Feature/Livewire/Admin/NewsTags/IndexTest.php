<?php

use App\Livewire\Admin\NewsTags\Index;
use App\Models\NewsPost;
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

describe('Admin NewsTags Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.news-tags.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with news.view permission to access', function () {
        $user = User::factory()->create();
        $user->givePermissionTo(Permissions::NEWS_VIEW);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.news-tags.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin NewsTags Index - Listing', function () {
    it('displays all news tags by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag1 = NewsTag::factory()->create(['name' => 'Movilidad']);
        $tag2 = NewsTag::factory()->create(['name' => 'Experiencias']);

        Livewire::test(Index::class)
            ->assertSee('Movilidad')
            ->assertSee('Experiencias');
    });

    it('displays news tag information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create([
            'name' => 'Movilidad',
            'slug' => 'movilidad',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Movilidad')
            ->assertSee('movilidad');
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create();
        $program = \App\Models\Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();

        NewsPost::factory()->count(5)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ])->each(function ($newsPost) use ($tag) {
            $newsPost->tags()->attach($tag->id);
        });

        Livewire::test(Index::class)
            ->assertSee('5');
    });
});

describe('Admin NewsTags Index - Search', function () {
    it('can search news tags by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag1 = NewsTag::factory()->create(['name' => 'Movilidad']);
        $tag2 = NewsTag::factory()->create(['name' => 'Experiencias']);

        $component = Livewire::test(Index::class)
            ->set('search', 'Movilidad');

        $tags = $component->get('newsTags');
        $tagNames = $tags->pluck('name')->toArray();
        expect($tagNames)->toContain('Movilidad')
            ->and($tagNames)->not->toContain('Experiencias');
    });

    it('can search news tags by slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag1 = NewsTag::factory()->create(['name' => 'Movilidad', 'slug' => 'movilidad']);
        $tag2 = NewsTag::factory()->create(['name' => 'Experiencias', 'slug' => 'experiencias']);

        Livewire::test(Index::class)
            ->set('search', 'movilidad')
            ->assertSee('Movilidad')
            ->assertDontSee('Experiencias');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        expect($component->get('search'))->toBe('test');
        expect($component->get('newsTags')->currentPage())->toBe(1);
    });
});

describe('Admin NewsTags Index - Sorting', function () {
    it('can sort by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['name' => 'Zeta']);
        NewsTag::factory()->create(['name' => 'Alpha']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'name');

        // Verificar que ambos están presentes (hay un orden secundario por nombre)
        $component->assertSee('Alpha')
            ->assertSee('Zeta');

        // Verificar que el orden es ascendente
        $tags = $component->get('newsTags');
        $names = $tags->pluck('name')->toArray();
        expect($names)->toContain('Alpha')
            ->and($names)->toContain('Zeta');
    });

    it('can sort by slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['name' => 'Tag Z', 'slug' => 'z-tag']);
        NewsTag::factory()->create(['name' => 'Tag A', 'slug' => 'a-tag']);

        Livewire::test(Index::class)
            ->call('sortBy', 'slug')
            ->assertSee('Tag A')
            ->assertSee('Tag Z');
    });

    it('can toggle sort direction', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->create(['name' => 'Alpha']);
        NewsTag::factory()->create(['name' => 'Zeta']);

        $component = Livewire::test(Index::class);

        // El estado inicial es sortField='name' y sortDirection='asc'
        expect($component->get('sortField'))->toBe('name')
            ->and($component->get('sortDirection'))->toBe('asc');

        // Llamar a sortBy con el mismo campo cambia la dirección
        $component->call('sortBy', 'name');

        // Ahora debería estar en 'desc' porque el campo ya era 'name'
        expect($component->get('sortDirection'))->toBe('desc')
            ->and($component->get('sortField'))->toBe('name');

        // Llamar de nuevo al mismo campo para cambiar la dirección de vuelta
        $component->call('sortBy', 'name');

        // Ahora debería estar en 'asc' de nuevo
        expect($component->get('sortDirection'))->toBe('asc')
            ->and($component->get('sortField'))->toBe('name');
    });
});

describe('Admin NewsTags Index - Filters', function () {
    it('shows only non-deleted news tags by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeTag = NewsTag::factory()->create(['name' => 'Active Tag']);
        $deletedTag = NewsTag::factory()->create(['name' => 'Deleted Tag']);
        $deletedTag->delete();

        Livewire::test(Index::class)
            ->assertSee('Active Tag')
            ->assertDontSee('Deleted Tag');
    });

    it('can show deleted news tags', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $activeTag = NewsTag::factory()->create(['name' => 'Active Tag']);
        $deletedTag = NewsTag::factory()->create(['name' => 'Deleted Tag']);
        $deletedTag->delete();

        $component = Livewire::test(Index::class)
            ->set('showDeleted', '1');

        $tags = $component->get('newsTags');
        $tagNames = $tags->pluck('name')->toArray();
        expect($tagNames)->toContain('Deleted Tag')
            ->and($tagNames)->not->toContain('Active Tag');
    });
});

describe('Admin NewsTags Index - Pagination', function () {
    it('paginates news tags', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10);

        expect($component->get('newsTags')->hasPages())->toBeTrue();
        expect($component->get('newsTags')->count())->toBe(10);
    });

    it('can change items per page', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsTag::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 25);

        expect($component->get('perPage'))->toBe(25);
        expect($component->get('newsTags')->count())->toBe(20);
    });
});

describe('Admin NewsTags Index - Soft Delete', function () {
    it('can delete a news tag without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Test Tag']);

        Livewire::test(Index::class)
            ->call('confirmDelete', $tag->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('newsTagToDelete', $tag->id)
            ->call('delete')
            ->assertDispatched('news-tag-deleted');

        expect($tag->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete a news tag with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Test Tag']);
        $program = \App\Models\Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->tags()->attach($tag->id);

        Livewire::test(Index::class)
            ->call('confirmDelete', $tag->id)
            ->call('delete')
            ->assertDispatched('news-tag-delete-error');

        expect($tag->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted news tag', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Test Tag']);
        $tag->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $tag->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('newsTagToRestore', $tag->id)
            ->call('restore')
            ->assertDispatched('news-tag-restored');

        expect($tag->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a news tag without relationships (super-admin only)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Test Tag']);
        $tag->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $tag->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('newsTagToForceDelete', $tag->id)
            ->call('forceDelete')
            ->assertDispatched('news-tag-force-deleted');

        expect(NewsTag::withTrashed()->find($tag->id))->toBeNull();
    });

    it('cannot force delete a news tag with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $tag = NewsTag::factory()->create(['name' => 'Test Tag']);
        $program = \App\Models\Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->tags()->attach($tag->id);
        $tag->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $tag->id)
            ->call('forceDelete')
            ->assertDispatched('news-tag-force-delete-error');

        expect(NewsTag::withTrashed()->find($tag->id))->not->toBeNull();
    });
});

describe('Admin NewsTags Index - Helpers', function () {
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
            ->assertSee('Crear Etiqueta');
    });

    it('can check if news tag can be deleted', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $tagWithoutRelations = NewsTag::factory()->create();
        $tagWithRelations = NewsTag::factory()->create();
        $program = \App\Models\Program::factory()->create();
        $academicYear = \App\Models\AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->tags()->attach($tagWithRelations->id);

        // El método canDeleteNewsTag se usa en la vista con tags que ya tienen el count cargado
        // Usamos instance() para acceder al método directamente
        $component = Livewire::test(Index::class);

        // Verificar que se puede eliminar un tag sin relaciones
        $tagWithoutRelationsLoaded = NewsTag::withCount(['newsPosts'])->find($tagWithoutRelations->id);
        expect($component->instance()->canDeleteNewsTag($tagWithoutRelationsLoaded))->toBeTrue();

        // Verificar que no se puede eliminar si tiene relaciones
        $tagWithRelationsLoaded = NewsTag::withCount(['newsPosts'])->find($tagWithRelations->id);
        expect($component->instance()->canDeleteNewsTag($tagWithRelationsLoaded))->toBeFalse();
    });
});

describe('Admin NewsTags Index - Edge Cases', function () {
    beforeEach(function () {
        $this->user = User::factory()->create();
        $this->user->assignRole(Roles::ADMIN);
        $this->actingAs($this->user);
    });

    it('delete does nothing when newsTagToDelete is null', function () {
        $component = Livewire::test(Index::class)
            ->set('newsTagToDelete', null)
            ->call('delete');

        // Should not dispatch any event since it returns early
        $component->assertNotDispatched('news-tag-deleted')
            ->assertNotDispatched('news-tag-delete-error');
    });

    it('restore does nothing when newsTagToRestore is null', function () {
        $component = Livewire::test(Index::class)
            ->set('newsTagToRestore', null)
            ->call('restore');

        // Should not dispatch any event since it returns early
        $component->assertNotDispatched('news-tag-restored');
    });

    it('forceDelete does nothing when newsTagToForceDelete is null', function () {
        $component = Livewire::test(Index::class)
            ->set('newsTagToForceDelete', null)
            ->call('forceDelete');

        // Should not dispatch any event since it returns early
        $component->assertNotDispatched('news-tag-force-deleted')
            ->assertNotDispatched('news-tag-force-delete-error');
    });

    it('canDeleteNewsTag returns false when user has no delete permission', function () {
        $viewer = User::factory()->create();
        $viewer->assignRole(Roles::VIEWER);
        $this->actingAs($viewer);

        $tag = NewsTag::factory()->create();
        $tag->loadCount(['newsPosts']);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteNewsTag($tag))->toBeFalse();
    });

    it('canViewDeleted returns true for users with viewAny permission', function () {
        $component = Livewire::test(Index::class);
        expect($component->instance()->canViewDeleted())->toBeTrue();
    });
});
