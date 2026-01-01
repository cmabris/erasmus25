<?php

use App\Livewire\Admin\News\Show;
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

describe('Admin News Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.news.show', $newsPost))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with NEWS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.news.show', $newsPost))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without NEWS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $this->get(route('admin.news.show', $newsPost))
            ->assertForbidden();
    });
});

describe('Admin News Show - Display', function () {
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
            'excerpt' => 'Resumen de la noticia',
            'content' => 'Contenido completo de la noticia',
            'status' => 'publicado',
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->assertSee('Noticia Test')
            ->assertSee('Resumen de la noticia')
            ->assertSee('Contenido completo de la noticia')
            ->assertSee('Programa Test')
            ->assertSee('2024-2025');
    });

    it('displays tags correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $tag1 = NewsTag::factory()->create(['name' => 'Etiqueta 1']);
        $tag2 = NewsTag::factory()->create(['name' => 'Etiqueta 2']);
        $newsPost->tags()->attach([$tag1->id, $tag2->id]);

        Livewire::test(Show::class, ['news_post' => $newsPost->load('tags')])
            ->assertSee('Etiqueta 1')
            ->assertSee('Etiqueta 2');
    });

    it('displays author and reviewer information', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $author = User::factory()->create(['name' => 'Autor Test']);
        $reviewer = User::factory()->create(['name' => 'Revisor Test']);
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'author_id' => $author->id,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost->load(['author', 'reviewer'])])
            ->assertSee('Autor Test')
            ->assertSee('Revisor Test');
    });

    it('displays mobility information when available', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'country' => 'España',
            'city' => 'Madrid',
            'host_entity' => 'Universidad Complutense',
            'mobility_type' => 'personal',
            'mobility_category' => 'curso',
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->assertSee('España')
            ->assertSee('Madrid')
            ->assertSee('Universidad Complutense')
            ->assertSee(__('Personal')) // mobility_type se traduce
            ->assertSee(__('Curso')); // mobility_category se traduce
    });
});

describe('Admin News Show - Publish/Unpublish', function () {
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('publish')
            ->assertDispatched('news-post-published');

        expect($newsPost->fresh())
            ->status->toBe('publicado')
            ->published_at->not->toBeNull();
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('unpublish')
            ->assertDispatched('news-post-unpublished');

        expect($newsPost->fresh())
            ->status->toBe('borrador')
            ->published_at->toBeNull();
    });

    it('requires publish permission to publish', function () {
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('publish')
            ->assertForbidden();
    });

    it('requires publish permission to unpublish', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'publicado',
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('unpublish')
            ->assertForbidden();
    });

    it('can toggle publish status', function () {
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('togglePublish');

        expect($newsPost->fresh()->status)->toBe('publicado');
    });
});

describe('Admin News Show - Delete', function () {
    it('can delete news post (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('delete')
            ->assertDispatched('news-post-deleted')
            ->assertRedirect(route('admin.news.index'));

        expect($newsPost->fresh()->trashed())->toBeTrue();
    });

    it('requires delete permission to delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('delete')
            ->assertForbidden();
    });
});

describe('Admin News Show - Restore', function () {
    it('can restore a soft deleted news post', function () {
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('restore')
            ->assertDispatched('news-post-restored');

        expect($newsPost->fresh()->trashed())->toBeFalse();
    });

    it('requires restore permission to restore', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->delete();

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('restore')
            ->assertForbidden();
    });
});

describe('Admin News Show - Force Delete', function () {
    it('can permanently delete a news post', function () {
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

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('forceDelete')
            ->assertDispatched('news-post-force-deleted')
            ->assertRedirect(route('admin.news.index'));

        expect(NewsPost::find($newsPost->id))->toBeNull();
    });

    it('requires forceDelete permission to permanently delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $newsPost->delete();

        Livewire::test(Show::class, ['news_post' => $newsPost])
            ->call('forceDelete')
            ->assertForbidden();
    });
});

describe('Admin News Show - Helper Methods', function () {
    it('can check if user can publish', function () {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $newsPost = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $component = Livewire::test(Show::class, ['news_post' => $newsPost]);
        expect($component->instance()->canPublish())->toBeTrue();
    });

    it('returns correct status color', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $published = NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'publicado',
        ]);

        $component = Livewire::test(Show::class, ['news_post' => $published]);
        expect($component->instance()->getStatusColor('publicado'))->toBe('success')
            ->and($component->instance()->getStatusColor('en_revision'))->toBe('warning')
            ->and($component->instance()->getStatusColor('archivado'))->toBe('neutral')
            ->and($component->instance()->getStatusColor('borrador'))->toBe('danger');
    });
});
