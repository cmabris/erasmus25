<?php

use App\Livewire\Admin\Translations\Index;
use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
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

describe('Admin Translations Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.translations.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with view permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.translations.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.translations.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Translations Index - Listing', function () {
    it('displays all translations by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        $translation1 = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa 1',
        ]);

        $translation2 = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'description',
            'value' => 'Descripción 1',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Programa 1')
            ->assertSee('Descripción 1');
    });

    it('displays translation information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create(['name' => 'Español', 'code' => 'es']);
        $program = Program::factory()->create(['name' => 'Programa Test', 'code' => 'TEST']);

        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa Test Traducido',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Programa Test Traducido')
            ->assertSee('Español')
            ->assertSee('es');
    });

    it('displays empty state when no translations exist', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertSee('No hay traducciones');
    });
});

describe('Admin Translations Index - Search', function () {
    it('filters translations by search term in field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa Test',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'description',
            'value' => 'Descripción Test',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'name')
            ->assertSee('Programa Test')
            ->assertDontSee('Descripción Test');
    });

    it('filters translations by search term in value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa Especial',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'description', // Diferente campo para evitar conflicto único
            'value' => 'Programa Normal',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Especial')
            ->assertSee('Programa Especial')
            ->assertDontSee('Programa Normal');
    });
});

describe('Admin Translations Index - Filters', function () {
    it('filters translations by model type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $setting = Setting::factory()->create();

        $programTranslation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa Test',
        ]);

        $settingTranslation = Translation::factory()->create([
            'translatable_type' => Setting::class,
            'translatable_id' => $setting->id,
            'language_id' => $language->id,
            'field' => 'value',
            'value' => 'Setting Test',
        ]);

        Livewire::test(Index::class)
            ->set('filterModel', Program::class)
            ->assertSee('Programa Test')
            ->assertDontSee('Setting Test');
    });

    it('filters translations by language', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language1 = Language::factory()->create(['name' => 'Español']);
        $language2 = Language::factory()->create(['name' => 'English']);
        $program = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language1->id,
            'field' => 'name',
            'value' => 'Programa ES',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language2->id,
            'field' => 'description', // Diferente campo para evitar conflicto único
            'value' => 'Program EN',
        ]);

        Livewire::test(Index::class)
            ->set('filterLanguageId', $language1->id)
            ->assertSee('Programa ES')
            ->assertDontSee('Program EN');
    });

    it('filters translations by translatable ID', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program1->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa 1',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program2->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Programa 2',
        ]);

        Livewire::test(Index::class)
            ->set('filterModel', Program::class)
            ->set('filterTranslatableId', $program1->id)
            ->assertSee('Programa 1')
            ->assertDontSee('Programa 2');
    });
});

describe('Admin Translations Index - Sorting', function () {
    it('sorts translations by field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program1->id,
            'language_id' => $language->id,
            'field' => 'description',
            'value' => 'B',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program2->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'A',
        ]);

        Livewire::test(Index::class)
            ->call('sortBy', 'field')
            ->assertSee('A')
            ->assertSee('B');
    });

    it('toggles sort direction when clicking same field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program1->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'A',
        ]);

        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program2->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'B',
        ]);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'field')
            ->assertSet('sortDirection', 'asc');

        $component->call('sortBy', 'field')
            ->assertSet('sortDirection', 'desc');
    });
});

describe('Admin Translations Index - Deletion', function () {
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

        Livewire::test(Index::class)
            ->call('confirmDelete', $translation->id)
            ->assertSet('showDeleteModal', true)
            ->assertSet('translationToDelete', $translation->id)
            ->call('delete')
            ->assertDispatched('translation-deleted');

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

        // El editor puede abrir el modal pero no puede eliminar
        // El método delete() tiene autorización que lanzará excepción
        try {
            Livewire::test(Index::class)
                ->call('confirmDelete', $translation->id)
                ->assertSet('showDeleteModal', true)
                ->call('delete');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            // Esperamos una excepción de autorización
        }

        // Verificamos que la traducción no fue eliminada
        expect(Translation::find($translation->id))->not->toBeNull();
    });
});

describe('Admin Translations Index - Pagination', function () {
    it('paginates translations correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $programs = Program::factory()->count(20)->create();

        // Crear más traducciones de las que caben en una página (default 15)
        foreach ($programs as $index => $program) {
            Translation::factory()->create([
                'translatable_type' => Program::class,
                'translatable_id' => $program->id,
                'language_id' => $language->id,
                'field' => 'name',
                'value' => "Programa {$index}",
            ]);
        }

        $component = Livewire::test(Index::class);
        
        // Verificamos que hay traducciones y que la paginación funciona
        expect($component->get('translations')->total())->toBeGreaterThan(15)
            ->and($component->get('translations')->count())->toBeLessThanOrEqual(15);
    });
});
