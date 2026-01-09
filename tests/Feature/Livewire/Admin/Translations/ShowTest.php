<?php

use App\Livewire\Admin\Translations\Show;
use App\Models\Language;
use App\Models\Program;
use App\Models\Translation;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear los permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::TRANSLATIONS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
        Permissions::TRANSLATIONS_CREATE,
        Permissions::TRANSLATIONS_EDIT,
        Permissions::TRANSLATIONS_DELETE,
    ]);

    // Editor puede ver, crear y editar
    $editor->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
        Permissions::TRANSLATIONS_CREATE,
        Permissions::TRANSLATIONS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::TRANSLATIONS_VIEW,
    ]);
});

describe('Admin Translations Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $this->get(route('admin.translations.show', $translation))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $this->get(route('admin.translations.show', $translation))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('allows viewer users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $this->get(route('admin.translations.show', $translation))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without view permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $this->get(route('admin.translations.show', $translation))
            ->assertForbidden();
    });
});

describe('Admin Translations Show - Display', function () {
    it('displays translation information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create(['name' => 'Español', 'code' => 'es']);
        $program = Program::factory()->create(['name' => 'Test Program', 'code' => 'TEST']);
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa Test Traducido',
        ]);

        Livewire::test(Show::class, ['translation' => $translation])
            ->assertSee('Programa Test Traducido')
            ->assertSee('Programa')
            ->assertSee('TEST')
            ->assertSee('Test Program')
            ->assertSee('name')
            ->assertSee('Español')
            ->assertSee('es');
    });

    it('displays translation value correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'This is a long translation value that should be displayed correctly',
        ]);

        Livewire::test(Show::class, ['translation' => $translation])
            ->assertSee('This is a long translation value that should be displayed correctly');
    });

    it('displays creation and update dates', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);

        Livewire::test(Show::class, ['translation' => $translation])
            ->assertSee($translation->created_at->format('d/m/Y'))
            ->assertSee($translation->updated_at->format('d/m/Y'));
    });
});

describe('Admin Translations Show - Deletion', function () {
    it('allows admin to delete translation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);

        Livewire::test(Show::class, ['translation' => $translation])
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('translation-deleted')
            ->assertRedirect(route('admin.translations.index'));

        expect(Translation::find($translation->id))->toBeNull();
    });

    it('denies editor to delete translation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Test',
        ]);

        // El editor no puede eliminar, pero el botón puede estar en el HTML aunque oculto
        // Verificamos que no puede ejecutar la acción
        Livewire::test(Show::class, ['translation' => $translation])
            ->assertSee('Test')
            ->call('confirmDelete')
            ->assertForbidden();
    });
});
