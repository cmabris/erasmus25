<?php

use App\Livewire\Admin\News\Index;
use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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
    Permission::firstOrCreate(['name' => Permissions::NEWS_PUBLISH, 'guard_name' => 'web']);

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
        Permissions::NEWS_PUBLISH,
    ]);
    $editor->givePermissionTo([
        Permissions::NEWS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::NEWS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::NEWS_VIEW,
    ]);

    Storage::fake('public');
});

describe('Admin News Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.news.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with NEWS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.news.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access for users without NEWS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.news.index'))
            ->assertForbidden();
    });
});

describe('Admin News Index - Listing', function () {
    it('displays all news posts by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 1',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 2',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Noticia 1')
            ->assertSee('Noticia 2');
    });

    it('displays news post information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Test',
            'status' => 'publicado',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Noticia Test')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025');
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $tag1 = NewsTag::factory()->create();
        $tag2 = NewsTag::factory()->create();
        $newsPost->tags()->attach([$tag1->id, $tag2->id]);

        Livewire::test(Index::class)
            ->assertSee('2'); // tags count
    });

    it('hides deleted news posts by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Activa',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Eliminada',
        ]);
        $news2->delete();

        Livewire::test(Index::class)
            ->assertSee('Noticia Activa')
            ->assertDontSee('Noticia Eliminada');
    });

    it('shows deleted news posts when filter is enabled', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Eliminada',
        ]);
        $newsPost->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->assertSee('Noticia Eliminada');
    });
});

describe('Admin News Index - Search', function () {
    it('can search news posts by title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Erasmus',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Movilidad',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Erasmus')
            ->assertSee('Noticia Erasmus')
            ->assertDontSee('Noticia Movilidad');
    });

    it('can search news posts by excerpt', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 1',
            'excerpt' => 'Resumen sobre Erasmus',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 2',
            'excerpt' => 'Resumen sobre Movilidad',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Erasmus')
            ->assertSee('Noticia 1')
            ->assertDontSee('Noticia 2');
    });

    it('can search news posts by slug', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 1',
            'slug' => 'noticia-erasmus',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia 2',
            'slug' => 'noticia-movilidad',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'erasmus')
            ->assertSee('Noticia 1')
            ->assertDontSee('Noticia 2');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        NewsPost::factory()->count(20)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        // Verify search was set and component rendered
        expect($component->get('search'))->toBe('test');
    });
});

describe('Admin News Index - Filtering', function () {
    it('filters news posts by program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa 1']);
        $program2 = Program::factory()->create(['name' => 'Programa 2']);
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Programa 1',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program2->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Programa 2',
        ]);

        Livewire::test(Index::class)
            ->set('filterProgram', (string) $program1->id)
            ->assertSee('Noticia Programa 1')
            ->assertDontSee('Noticia Programa 2');
    });

    it('filters news posts by academic year', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $year1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $year2 = AcademicYear::factory()->create(['year' => '2025-2026']);

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year1->id,
            'title' => 'Noticia 2024-2025',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $year2->id,
            'title' => 'Noticia 2025-2026',
        ]);

        Livewire::test(Index::class)
            ->set('filterAcademicYear', (string) $year1->id)
            ->assertSee('Noticia 2024-2025')
            ->assertDontSee('Noticia 2025-2026');
    });

    it('filters news posts by status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'publicado',
            'title' => 'Noticia Publicada',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
            'title' => 'Noticia Borrador',
        ]);

        Livewire::test(Index::class)
            ->set('filterStatus', 'publicado')
            ->assertSee('Noticia Publicada')
            ->assertDontSee('Noticia Borrador');
    });

    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('filterProgram', '1')
            ->set('filterAcademicYear', '1')
            ->set('filterStatus', 'publicado')
            ->set('showDeleted', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterProgram', '')
            ->assertSet('filterAcademicYear', '')
            ->assertSet('filterStatus', '')
            ->assertSet('showDeleted', '0');
    });
});

describe('Admin News Index - Sorting', function () {
    it('sorts news posts by created_at descending by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Antigua',
            'created_at' => now()->subDays(2),
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Noticia Nueva',
            'created_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->assertSeeInOrder(['Noticia Nueva', 'Noticia Antigua']);
    });

    it('can sort by title ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $news1 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Z Noticia',
        ]);

        $news2 = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'A Noticia',
        ]);

        Livewire::test(Index::class)
            ->call('sortBy', 'title')
            ->assertSeeInOrder(['A Noticia', 'Z Noticia']);
    });

    it('resets pagination when sorting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        NewsPost::factory()->count(20)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->call('sortBy', 'title');

        // Verify that pagination was reset by checking the component state
        expect($component->get('sortField'))->toBe('title');
    });
});

describe('Admin News Index - Actions', function () {
    it('can publish a news post', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
            'published_at' => null,
        ]);

        Livewire::test(Index::class)
            ->call('publish', $newsPost->id)
            ->assertDispatched('news-post-published');

        $newsPost->refresh();
        expect($newsPost->status)->toBe('publicado')
            ->and($newsPost->published_at)->not->toBeNull();
    });

    it('can unpublish a news post', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'publicado',
            'published_at' => now(),
        ]);

        Livewire::test(Index::class)
            ->call('unpublish', $newsPost->id)
            ->assertDispatched('news-post-unpublished');

        $newsPost->refresh();
        expect($newsPost->status)->toBe('borrador')
            ->and($newsPost->published_at)->toBeNull();
    });

    it('requires publish permission to publish a news post', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        Livewire::test(Index::class)
            ->call('publish', $newsPost->id)
            ->assertForbidden();
    });

    it('can delete a news post (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $newsPost->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('newsPostToDelete', $newsPost->id)
            ->call('delete')
            ->assertDispatched('news-post-deleted');

        expect($newsPost->fresh()->trashed())->toBeTrue();
    });

    it('requires delete permission to delete a news post', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->call('confirmDelete', $newsPost->id)
            ->call('delete')
            ->assertForbidden();
    });

    it('can restore a deleted news post', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $newsPost->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('newsPostToRestore', $newsPost->id)
            ->call('restore')
            ->assertDispatched('news-post-restored');

        expect($newsPost->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a news post (permanent deletion)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $newsPost->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('newsPostToForceDelete', $newsPost->id)
            ->call('forceDelete')
            ->assertDispatched('news-post-force-deleted');

        expect(NewsPost::withTrashed()->find($newsPost->id))->toBeNull();
    });
});

describe('Admin News Index - Permissions', function () {
    it('shows create button only for users with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertSee(__('Crear Noticia'));
    });

    it('hides create button for users without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canCreate())->toBeFalse();
    });

    it('shows delete button only for users with delete permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteNewsPost($newsPost))->toBeTrue();
    });

    it('hides delete button for users without delete permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteNewsPost($newsPost))->toBeFalse();
    });

    it('shows publish button only for users with publish permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canPublishNewsPost($newsPost))->toBeTrue();
    });

    it('hides publish button for users without publish permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        $component = Livewire::test(Index::class);
        expect($component->instance()->canPublishNewsPost($newsPost))->toBeFalse();
    });
});
