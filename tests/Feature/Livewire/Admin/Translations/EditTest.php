<?php

use App\Livewire\Admin\Translations\Edit;
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

describe('Admin Translations Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $language = Language::factory()->create();
        $program = Program::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $this->get(route('admin.translations.edit', $translation))
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

        $this->get(route('admin.translations.edit', $translation))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without edit permission', function () {
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

        $this->get(route('admin.translations.edit', $translation))
            ->assertForbidden();
    });
});

describe('Admin Translations Edit - Successful Update', function () {
    it('can update translation value', function () {
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
            'value' => 'Old Value',
        ]);

        Livewire::test(Edit::class, ['translation' => $translation])
            ->set('value', 'New Value')
            ->call('update')
            ->assertDispatched('translation-updated')
            ->assertRedirect(route('admin.translations.index'));

        expect($translation->fresh()->value)->toBe('New Value');
    });

    it('preserves other translation properties when updating', function () {
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
            'value' => 'Old Value',
        ]);

        $originalType = $translation->translatable_type;
        $originalId = $translation->translatable_id;
        $originalLanguageId = $translation->language_id;
        $originalField = $translation->field;

        Livewire::test(Edit::class, ['translation' => $translation])
            ->set('value', 'New Value')
            ->call('update');

        $translation->refresh();
        expect($translation->translatable_type)->toBe($originalType)
            ->and($translation->translatable_id)->toBe($originalId)
            ->and($translation->language_id)->toBe($originalLanguageId)
            ->and($translation->field)->toBe($originalField)
            ->and($translation->value)->toBe('New Value');
    });
});

describe('Admin Translations Edit - Validation', function () {
    it('validates value is required', function () {
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
            'value' => 'Old Value',
        ]);

        Livewire::test(Edit::class, ['translation' => $translation])
            ->set('value', '')
            ->call('update')
            ->assertHasErrors(['value']);
    });

    it('validates value is string', function () {
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
            'value' => 'Old Value',
        ]);

        Livewire::test(Edit::class, ['translation' => $translation])
            ->set('value', 'New Value')
            ->call('update')
            ->assertHasNoErrors();
    });
});

describe('Admin Translations Edit - Display', function () {
    it('displays current translation value', function () {
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
            'value' => 'Current Value',
        ]);

        Livewire::test(Edit::class, ['translation' => $translation])
            ->assertSet('value', 'Current Value');
    });

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
            'value' => 'Test Value',
        ]);

        Livewire::test(Edit::class, ['translation' => $translation])
            ->assertSee('Test Program')
            ->assertSee('Español')
            ->assertSee('name');
    });
});

describe('Admin Translations Edit - Helper Methods', function () {
    it('returns correct model type display name for Program', function () {
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

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getModelTypeDisplayName(Program::class);

        expect($displayName)->toBe(__('Programa'));
    });

    it('returns correct model type display name for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $setting = \App\Models\Setting::factory()->create();
        $translation = Translation::factory()->create([
            'translatable_type' => \App\Models\Setting::class,
            'translatable_id' => $setting->id,
            'language_id' => $language->id,
        ]);

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getModelTypeDisplayName(\App\Models\Setting::class);

        expect($displayName)->toBe(__('Configuración'));
    });

    it('returns class basename for unknown model type', function () {
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

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getModelTypeDisplayName(\App\Models\User::class);

        expect($displayName)->toBe('User');
    });

    it('returns dash for null model type', function () {
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

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getModelTypeDisplayName(null);

        expect($displayName)->toBe('-');
    });

    it('returns correct translatable display name for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create(['code' => 'KA131', 'name' => 'Test Program']);
        $translation = Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
        ]);

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getTranslatableDisplayName();

        expect($displayName)->toBe('KA131 - Test Program');
    });

    it('returns correct translatable display name for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $setting = \App\Models\Setting::factory()->create(['key' => 'site_title']);
        $translation = Translation::factory()->create([
            'translatable_type' => \App\Models\Setting::class,
            'translatable_id' => $setting->id,
            'language_id' => $language->id,
        ]);

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getTranslatableDisplayName();

        expect($displayName)->toBe('site_title');
    });

    it('returns deleted message for null translatable', function () {
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

        // Delete the program to make translatable null
        $program->forceDelete();
        $translation->refresh();

        $component = Livewire::test(Edit::class, ['translation' => $translation]);
        $displayName = $component->instance()->getTranslatableDisplayName();

        expect($displayName)->toBe(__('Registro eliminado'));
    });
});
