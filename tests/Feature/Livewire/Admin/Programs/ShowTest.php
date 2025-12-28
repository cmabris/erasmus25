<?php

use App\Livewire\Admin\Programs\Show;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
        Permissions::PROGRAMS_DELETE,
    ]);
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::PROGRAMS_CREATE,
        Permissions::PROGRAMS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
    ]);

    Storage::fake('public');
});

describe('Admin Programs Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();

        $this->get(route('admin.programs.show', $program))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with PROGRAMS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $this->get(route('admin.programs.show', $program))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without PROGRAMS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $program = Program::factory()->create();

        $this->get(route('admin.programs.show', $program))
            ->assertForbidden();
    });
});

describe('Admin Programs Show - Display', function () {
    it('displays program information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'code' => 'KA121-SCH',
            'name' => 'Movilidad Educación Escolar',
            'description' => 'Descripción del programa',
            'is_active' => true,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->assertSee('KA121-SCH')
            ->assertSee('Movilidad Educación Escolar')
            ->assertSee('Descripción del programa');
    });

    it('displays program image if available', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('hasImage', true);
    });

    it('displays statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->count(5)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'abierta',
        ]);

        Call::factory()->count(3)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'cerrada',
        ]);

        NewsPost::factory()->count(4)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'publicado',
        ]);

        NewsPost::factory()->count(2)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador',
        ]);

        $component = Livewire::test(Show::class, ['program' => $program]);
        $statistics = $component->get('statistics');

        expect($statistics)
            ->total_calls->toBe(8)
            ->active_calls->toBe(5)
            ->total_news->toBe(6)
            ->published_news->toBe(4);
    });

    it('loads related calls and news posts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->count(10)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        NewsPost::factory()->count(10)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->assertSee('Convocatorias')
            ->assertSee('Noticias');
    });
});

describe('Admin Programs Show - Toggle Active', function () {
    it('can toggle active status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['is_active' => true]);

        Livewire::test(Show::class, ['program' => $program])
            ->call('toggleActive')
            ->assertDispatched('program-updated');

        expect($program->fresh()->is_active)->toBeFalse();
    });

    it('requires update permission to toggle active status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create(['is_active' => true]);

        Livewire::test(Show::class, ['program' => $program])
            ->call('toggleActive')
            ->assertForbidden();
    });
});

describe('Admin Programs Show - Soft Delete', function () {
    it('can delete a program without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('program-deleted')
            ->assertRedirect(route('admin.programs.index'));

        expect($program->fresh()->trashed())->toBeTrue();
    });

    it('cannot delete a program with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('program-delete-error');

        expect($program->fresh()->trashed())->toBeFalse();
    });

    it('requires delete permission to delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showDeleteModal', true)
            ->call('delete')
            ->assertForbidden();
    });
});

describe('Admin Programs Show - Restore', function () {
    it('can restore a deleted program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showRestoreModal', true)
            ->call('restore')
            ->assertDispatched('program-restored')
            ->assertRedirect(route('admin.programs.show', $program));

        expect($program->fresh()->trashed())->toBeFalse();
    });

    it('requires delete permission to restore', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showRestoreModal', true)
            ->call('restore')
            ->assertForbidden();
    });
});

describe('Admin Programs Show - Force Delete', function () {
    it('can force delete a program without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('program-force-deleted')
            ->assertRedirect(route('admin.programs.index'));

        expect(Program::withTrashed()->find($program->id))->toBeNull();
    });

    it('cannot force delete a program with relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();
        $program->delete();

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('program-force-delete-error');

        expect(Program::withTrashed()->find($program->id))->not->toBeNull();
    });

    it('requires super-admin role to force delete', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Show::class, ['program' => $program])
            ->set('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertForbidden();
    });
});

describe('Admin Programs Show - Relationships', function () {
    it('correctly identifies when program has relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('hasRelationships', true);
    });

    it('correctly identifies when program has no relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('hasRelationships', false);
    });

    it('checks both calls and news posts for relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        // Only news posts
        NewsPost::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('hasRelationships', true);
    });
});

describe('Admin Programs Show - Image URLs', function () {
    it('returns image URL when program has image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('imageUrl', fn ($value) => $value !== null);
    });

    it('returns null when program has no image', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Show::class, ['program' => $program])
            ->assertSet('imageUrl', null);
    });

    it('can get image URL with conversion', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $image = UploadedFile::fake()->image('program.jpg');
        $program->addMedia($image->getRealPath())
            ->usingName($program->name)
            ->toMediaCollection('image');

        $component = Livewire::test(Show::class, ['program' => $program]);
        $url = $component->instance()->getImageUrl('large');

        expect($url)->not->toBeNull();
    });
});
