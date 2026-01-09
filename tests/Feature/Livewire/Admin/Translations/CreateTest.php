<?php

use App\Livewire\Admin\Translations\Create;
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

describe('Admin Translations Create - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.translations.create'))
            ->assertRedirect('/login');
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.translations.create'))
            ->assertSuccessful()
            ->assertSeeLivewire(Create::class);
    });

    it('denies access for users without create permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.translations.create'))
            ->assertForbidden();
    });
});

describe('Admin Translations Create - Successful Creation', function () {
    it('can create a translation with valid data for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('languageId', $language->id)
            ->set('field', 'name')
            ->set('value', 'Programa Test')
            ->call('store')
            ->assertDispatched('translation-created')
            ->assertRedirect(route('admin.translations.index'));

        expect(Translation::where('value', 'Programa Test')->exists())->toBeTrue();
    });

    it('can create a translation with valid data for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $setting = Setting::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Setting::class)
            ->set('translatableId', $setting->id)
            ->set('languageId', $language->id)
            ->set('field', 'value')
            ->set('value', 'Setting Value')
            ->call('store')
            ->assertDispatched('translation-created')
            ->assertRedirect(route('admin.translations.index'));

        expect(Translation::where('value', 'Setting Value')->exists())->toBeTrue();
    });
});

describe('Admin Translations Create - Validation', function () {
    it('validates required fields', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Create::class)
            ->call('store')
            ->assertHasErrors(['translatableType', 'translatableId', 'languageId', 'field', 'value']);
    });

    it('validates translatable_type is valid', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', 'InvalidType')
            ->set('translatableId', $program->id)
            ->set('languageId', $language->id)
            ->set('field', 'name')
            ->set('value', 'Test')
            ->call('store')
            ->assertHasErrors(['translatableType']);
    });

    it('validates translatable_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();

        // La validación se hace en el FormRequest
        // El componente captura la excepción y la mapea a errores de Livewire
        $initialCount = Translation::count();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', 99999)
            ->set('languageId', $language->id)
            ->set('field', 'name')
            ->set('value', 'Test')
            ->call('store');

        // Verificamos que no se creó la traducción
        expect(Translation::count())->toBe($initialCount);
    });

    it('validates language_id exists', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('languageId', 99999)
            ->set('field', 'name')
            ->set('value', 'Test')
            ->call('store')
            ->assertHasErrors(['languageId']);
    });

    it('validates field is valid for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // La validación se hace en el FormRequest
        // Verificamos que no se crea la traducción con campo inválido
        $initialCount = Translation::count();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('languageId', $language->id)
            ->set('field', 'invalid_field')
            ->set('value', 'Test')
            ->call('store');

        expect(Translation::count())->toBe($initialCount);
    });

    it('validates field is valid for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $setting = Setting::factory()->create();

        // La validación se hace en el FormRequest
        // Verificamos que no se crea la traducción con campo inválido
        $initialCount = Translation::count();

        Livewire::test(Create::class)
            ->set('translatableType', Setting::class)
            ->set('translatableId', $setting->id)
            ->set('languageId', $language->id)
            ->set('field', 'invalid_field')
            ->set('value', 'Test')
            ->call('store');

        expect(Translation::count())->toBe($initialCount);
    });
});

describe('Admin Translations Create - Uniqueness', function () {
    it('prevents creating duplicate translation', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Crear traducción existente
        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Existing',
        ]);

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('languageId', $language->id)
            ->set('field', 'name')
            ->set('value', 'New')
            ->call('store')
            ->assertHasErrors(['value']);
    });

    it('allows creating translation with different field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $language = Language::factory()->create();
        $program = Program::factory()->create();

        // Crear traducción existente para 'name'
        Translation::factory()->create([
            'translatable_type' => Program::class,
            'translatable_id' => $program->id,
            'language_id' => $language->id,
            'field' => 'name',
            'value' => 'Existing',
        ]);

        // Debe permitir crear para 'description'
        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('languageId', $language->id)
            ->set('field', 'description')
            ->set('value', 'New Description')
            ->call('store')
            ->assertDispatched('translation-created');

        expect(Translation::where('field', 'description')->where('value', 'New Description')->exists())->toBeTrue();
    });
});

describe('Admin Translations Create - Dynamic Selectors', function () {
    it('resets translatableId when translatableType changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->set('translatableType', Setting::class)
            ->assertSet('translatableId', null)
            ->assertSet('field', '');
    });

    it('resets field when translatableId changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program1->id)
            ->set('field', 'name')
            ->set('translatableId', $program2->id)
            ->assertSet('field', '');
    });

    it('shows correct translatable options for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Test Program', 'code' => 'TEST']);

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->assertSee('TEST')
            ->assertSee('Test Program');
    });

    it('shows correct translatable options for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create(['key' => 'test_setting']);

        Livewire::test(Create::class)
            ->set('translatableType', Setting::class)
            ->assertSee('test_setting');
    });

    it('shows correct fields for Program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Program::class)
            ->set('translatableId', $program->id)
            ->assertSee('name')
            ->assertSee('description');
    });

    it('shows correct fields for Setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create();

        Livewire::test(Create::class)
            ->set('translatableType', Setting::class)
            ->set('translatableId', $setting->id)
            ->assertSee('value');
    });
});
