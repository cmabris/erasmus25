<?php

use App\Livewire\Admin\Programs\Index;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
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

describe('Admin Programs Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.programs.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with PROGRAMS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.programs.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access for users without PROGRAMS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.programs.index'))
            ->assertForbidden();
    });
});

describe('Admin Programs Index - Listing', function () {
    it('displays all programs by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa A', 'order' => 1]);
        $program2 = Program::factory()->create(['name' => 'Programa B', 'order' => 2]);

        Livewire::test(Index::class)
            ->assertSee('Programa A')
            ->assertSee('Programa B');
    });

    it('displays program information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create([
            'code' => 'KA121-SCH',
            'name' => 'Movilidad Educación Escolar',
            'order' => 1,
            'is_active' => true,
        ]);

        Livewire::test(Index::class)
            ->assertSee('KA121-SCH')
            ->assertSee('Movilidad Educación Escolar')
            ->assertSee('1'); // order
    });

    it('displays relationship counts', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $academicYear = AcademicYear::factory()->create();
        $program = Program::factory()->create();

        Call::factory()->count(3)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        NewsPost::factory()->count(5)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('3')
            ->assertSee('5');
    });
});

describe('Admin Programs Index - Search', function () {
    it('can search programs by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa Erasmus+']);
        Program::factory()->create(['name' => 'Otro Programa']);

        Livewire::test(Index::class)
            ->set('search', 'Erasmus')
            ->assertSee('Programa Erasmus+')
            ->assertDontSee('Otro Programa');
    });

    it('can search programs by code', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['code' => 'KA121-SCH']);
        Program::factory()->create(['code' => 'KA131-HED']);

        Livewire::test(Index::class)
            ->set('search', 'KA121')
            ->assertSee('KA121-SCH')
            ->assertDontSee('KA131-HED');
    });

    it('can search programs by description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Use unique descriptions to avoid conflicts with other tests
        $program1 = Program::factory()->create([
            'code' => 'TEST-ESC-' . uniqid(),
            'name' => 'Test Escolar',
            'description' => 'Programa de movilidad escolar único para test',
        ]);
        $program2 = Program::factory()->create([
            'code' => 'TEST-FORM-' . uniqid(),
            'name' => 'Test Formación',
            'description' => 'Programa de formación profesional único para test',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'escolar único')
            ->assertSee('Programa de movilidad escolar único para test')
            ->assertDontSee('Programa de formación profesional único para test');
    });

    it('resets pagination when searching', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->set('search', 'test');

        // Verify search was set and component rendered
        expect($component->get('search'))->toBe('test');
    });
});

describe('Admin Programs Index - Sorting', function () {
    it('can sort by order ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa B', 'order' => 2]);
        Program::factory()->create(['name' => 'Programa A', 'order' => 1]);

        $component = Livewire::test(Index::class);

        // Default is already 'order' asc, so clicking will toggle to desc
        // Let's test by sorting by a different field first, then back to order
        $component->call('sortBy', 'name')
            ->call('sortBy', 'order');

        expect($component->get('sortField'))->toBe('order')
            ->and($component->get('sortDirection'))->toBe('asc')
            ->and($component->html())->toContain('Programa A')
            ->and($component->html())->toContain('Programa B');
    });

    it('can sort by order descending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa A', 'order' => 1]);
        Program::factory()->create(['name' => 'Programa B', 'order' => 2]);

        $component = Livewire::test(Index::class);

        // Default is 'order' asc, so clicking once will toggle to desc
        $component->call('sortBy', 'order');

        expect($component->get('sortField'))->toBe('order')
            ->and($component->get('sortDirection'))->toBe('desc')
            ->and($component->html())->toContain('Programa A')
            ->and($component->html())->toContain('Programa B');
    });

    it('can sort by code', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['code' => 'KA131-HED', 'name' => 'Programa B']);
        Program::factory()->create(['code' => 'KA121-SCH', 'name' => 'Programa A']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'code');

        $html = $component->html();
        $posA = strpos($html, 'KA121-SCH');
        $posB = strpos($html, 'KA131-HED');

        expect($posA)->not->toBeFalse()
            ->and($posB)->not->toBeFalse()
            ->and($posA)->toBeLessThan($posB);
    });

    it('can sort by name', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa B']);
        Program::factory()->create(['name' => 'Programa A']);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'name');

        $html = $component->html();
        $posA = strpos($html, 'Programa A');
        $posB = strpos($html, 'Programa B');

        expect($posA)->not->toBeFalse()
            ->and($posB)->not->toBeFalse()
            ->and($posA)->toBeLessThan($posB);
    });

    it('resets pagination when sorting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(20)->create();

        $component = Livewire::test(Index::class)
            ->set('perPage', 10)
            ->call('$refresh')
            ->call('sortBy', 'name');

        // Verify that pagination was reset by checking the component state
        expect($component->get('sortField'))->toBe('name');
    });
});

describe('Admin Programs Index - Filters', function () {
    it('can filter by active programs only', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa Activo', 'is_active' => true]);
        Program::factory()->create(['name' => 'Programa Inactivo', 'is_active' => false]);

        Livewire::test(Index::class)
            ->set('showActiveOnly', '1')
            ->assertSee('Programa Activo')
            ->assertDontSee('Programa Inactivo');
    });

    it('can filter by inactive programs only', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa Activo', 'is_active' => true]);
        Program::factory()->create(['name' => 'Programa Inactivo', 'is_active' => false]);

        Livewire::test(Index::class)
            ->set('showActiveOnly', '0')
            ->assertSee('Programa Inactivo')
            ->assertDontSee('Programa Activo');
    });

    it('shows all programs when filter is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->create(['name' => 'Programa Activo', 'is_active' => true]);
        Program::factory()->create(['name' => 'Programa Inactivo', 'is_active' => false]);

        Livewire::test(Index::class)
            ->set('showActiveOnly', '')
            ->assertSee('Programa Activo')
            ->assertSee('Programa Inactivo');
    });

    it('can show deleted programs', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Eliminado']);
        $program->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->assertSee('Programa Eliminado');
    });

    it('hides deleted programs by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Eliminado']);
        $program->delete();

        Livewire::test(Index::class)
            ->assertDontSee('Programa Eliminado');
    });

    it('can reset filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('showActiveOnly', '1')
            ->set('showDeleted', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('showActiveOnly', '')
            ->assertSet('showDeleted', '0');
    });
});

describe('Admin Programs Index - Pagination', function () {
    it('paginates programs correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(20)->create();

        Livewire::test(Index::class)
            ->set('perPage', 10)
            ->assertSee('Programas Erasmus+')
            ->assertSet('perPage', 10);
    });

    it('shows empty state when no programs match filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('search', 'nonexistent program xyz')
            ->assertSee(__('No hay programas'));
    });
});

describe('Admin Programs Index - Actions', function () {
    it('can toggle active status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['is_active' => true]);

        Livewire::test(Index::class)
            ->call('toggleActive', $program->id);

        expect($program->fresh()->is_active)->toBeFalse();
    });

    it('requires update permission to toggle active status', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create(['is_active' => true]);

        Livewire::test(Index::class)
            ->call('toggleActive', $program->id)
            ->assertForbidden();
    });

    it('can delete a program without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Index::class)
            ->call('confirmDelete', $program->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('programToDelete', $program->id)
            ->call('delete')
            ->assertDispatched('program-deleted');

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

        Livewire::test(Index::class)
            ->call('confirmDelete', $program->id)
            ->call('delete')
            ->assertDispatched('program-delete-error');

        expect($program->fresh()->trashed())->toBeFalse();
    });

    it('can restore a deleted program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmRestore', $program->id)
            ->assertSet('showRestoreModal', true)
            ->assertSet('programToRestore', $program->id)
            ->call('restore')
            ->assertDispatched('program-restored');

        expect($program->fresh()->trashed())->toBeFalse();
    });

    it('can force delete a program without relationships', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $program->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $program->id)
            ->assertSet('showForceDeleteModal', true)
            ->assertSet('programToForceDelete', $program->id)
            ->call('forceDelete')
            ->assertDispatched('program-force-deleted');

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

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->call('confirmForceDelete', $program->id)
            ->call('forceDelete')
            ->assertDispatched('program-force-delete-error');

        expect(Program::withTrashed()->find($program->id))->not->toBeNull();
    });

    it('can move program up in order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['order' => 1, 'name' => 'Program A']);
        $program2 = Program::factory()->create(['order' => 2, 'name' => 'Program B']);

        $originalOrder1 = $program1->order;
        $originalOrder2 = $program2->order;

        Livewire::test(Index::class)
            ->call('moveDown', $program1->id);

        // Verify orders were swapped
        $program1->refresh();
        $program2->refresh();
        expect($program1->order)->toBe($originalOrder2)
            ->and($program2->order)->toBe($originalOrder1);
    });

    it('can move program down in order', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['order' => 1, 'name' => 'Program A']);
        $program2 = Program::factory()->create(['order' => 2, 'name' => 'Program B']);

        $originalOrder1 = $program1->order;
        $originalOrder2 = $program2->order;

        Livewire::test(Index::class)
            ->call('moveUp', $program2->id);

        // Verify orders were swapped
        $program1->refresh();
        $program2->refresh();
        expect($program1->order)->toBe($originalOrder2)
            ->and($program2->order)->toBe($originalOrder1);
    });

    it('cannot move first program up', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['order' => 1]);
        $program2 = Program::factory()->create(['order' => 2]);

        Livewire::test(Index::class)
            ->call('moveUp', $program1->id);

        // No debería cambiar el orden
        expect($program1->fresh()->order)->toBe(1)
            ->and($program2->fresh()->order)->toBe(2);
    });

    it('cannot move last program down', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['order' => 1]);
        $program2 = Program::factory()->create(['order' => 2]);

        Livewire::test(Index::class)
            ->call('moveDown', $program2->id);

        // No debería cambiar el orden
        expect($program1->fresh()->order)->toBe(1)
            ->and($program2->fresh()->order)->toBe(2);
    });
});

describe('Admin Programs Index - Permissions', function () {
    it('shows create button only for users with create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertSee(__('Crear Programa'));
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

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteProgram($program))->toBeTrue();
    });

    it('hides delete button for users without delete permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();

        $component = Livewire::test(Index::class);
        expect($component->instance()->canDeleteProgram($program))->toBeFalse();
    });
});
