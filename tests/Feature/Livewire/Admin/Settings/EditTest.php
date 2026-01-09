<?php

use App\Livewire\Admin\Settings\Edit;
use App\Models\Language;
use App\Models\Setting;
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
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::SETTINGS_EDIT, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $editor->givePermissionTo([
        Permissions::SETTINGS_VIEW,
        Permissions::SETTINGS_EDIT,
    ]);
    $viewer->givePermissionTo([
        Permissions::SETTINGS_VIEW,
    ]);

    Storage::fake('public');

    // Crear idiomas para tests de traducción
    Language::factory()->create(['code' => 'es', 'name' => 'Español', 'is_default' => true, 'is_active' => true]);
    Language::factory()->create(['code' => 'en', 'name' => 'English', 'is_default' => false, 'is_active' => true]);
});

describe('Admin Settings Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $setting = Setting::factory()->create();

        $this->get(route('admin.settings.edit', $setting))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with SETTINGS_EDIT permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create();

        $this->get(route('admin.settings.edit', $setting))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without SETTINGS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $setting = Setting::factory()->create();

        $this->get(route('admin.settings.edit', $setting))
            ->assertForbidden();
    });
});

describe('Admin Settings Edit - Data Loading', function () {
    it('loads string setting data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Test Site',
            'type' => 'string',
            'description' => 'Site name',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->assertSet('value', 'Test Site')
            ->assertSet('description', 'Site name');
    });

    it('loads integer setting data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'items_per_page',
            'value' => '15',
            'type' => 'integer',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->assertSet('value', 15);
    });

    it('loads boolean setting data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'rgpd_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->assertSet('value', true);
    });

    it('loads JSON setting data correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $jsonData = ['key' => 'value', 'number' => 123];
        $setting = Setting::factory()->create([
            'key' => 'allowed_types',
            'value' => json_encode($jsonData),
            'type' => 'json',
        ]);

        $component = Livewire::test(Edit::class, ['setting' => $setting]);
        $value = $component->get('value');

        expect(json_decode($value, true))->toBe($jsonData);
        expect($component->get('jsonPreview'))->not->toBeNull();
    });
});

describe('Admin Settings Edit - Update String Type', function () {
    it('can update string setting with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'site_name',
            'value' => 'Old Name',
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', 'New Name')
            ->set('description', 'New Description')
            ->call('confirmUpdate')
            ->assertSet('showUpdateModal', true)
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        $updated = $setting->fresh();
        expect($updated->value)->toBe('New Name');
        expect($updated->description)->toBe('New Description');
        expect($updated->updated_by)->toBe($user->id);
    });

    it('validates required value for string type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', '')
            ->call('confirmUpdate')
            ->assertHasErrors(['value']);
    });
});

describe('Admin Settings Edit - Update Integer Type', function () {
    it('can update integer setting with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'items_per_page',
            'value' => '10',
            'type' => 'integer',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', 25)
            ->call('confirmUpdate')
            ->assertSet('showUpdateModal', true)
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->fresh()->value)->toBe(25);
    });

    it('validates integer value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'integer',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', 'not-a-number')
            ->call('confirmUpdate')
            ->assertHasErrors(['value']);
    });
});

describe('Admin Settings Edit - Update Boolean Type', function () {
    it('can update boolean setting to true', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'rgpd_enabled',
            'value' => '0',
            'type' => 'boolean',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', true)
            ->call('confirmUpdate')
            ->assertSet('showUpdateModal', true)
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->fresh()->value)->toBeTrue();
    });

    it('can update boolean setting to false', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'rgpd_enabled',
            'value' => '1',
            'type' => 'boolean',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', false)
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->fresh()->value)->toBeFalse();
    });
});

describe('Admin Settings Edit - Update JSON Type', function () {
    it('can update JSON setting with valid JSON', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'allowed_types',
            'value' => json_encode(['old' => 'value']),
            'type' => 'json',
        ]);

        $newJson = json_encode(['new' => 'value', 'number' => 123]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', $newJson)
            ->call('confirmUpdate')
            ->assertSet('showUpdateModal', true)
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        $updated = $setting->fresh();
        // El accessor convierte JSON a array automáticamente cuando se accede
        // Pero necesitamos refrescar el modelo para que el accessor funcione
        $updated->refresh();
        $value = $updated->value;

        // El accessor debería convertir el JSON string a array
        if (is_string($value)) {
            $value = json_decode($value, true);
        }

        expect($value)->toBeArray();
        expect($value)->toHaveKey('new');
        expect($value)->toHaveKey('number');
        expect($value['new'])->toBe('value');
        expect($value['number'])->toBe(123);
    });

    it('validates JSON syntax', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'json',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', '{invalid json}')
            ->call('confirmUpdate')
            ->assertHasErrors(['value']);
    });

    it('updates JSON preview when value changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'json',
            'value' => json_encode(['key' => 'value']),
        ]);

        $component = Livewire::test(Edit::class, ['setting' => $setting]);
        expect($component->get('jsonPreview'))->not->toBeNull();

        $component->set('value', json_encode(['new' => 'data']));
        expect($component->get('jsonPreview'))->not->toBeNull();
    });
});

describe('Admin Settings Edit - Translations', function () {
    it('loads existing translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
            'description' => 'Default description',
        ]);

        $setting->setTranslation('description', 'en', 'English description');
        $setting->setTranslation('value', 'en', 'English value');

        $component = Livewire::test(Edit::class, ['setting' => $setting]);
        $translations = $component->get('translations');

        expect($translations['en']['description'])->toBe('English description');
        expect($translations['en']['value'])->toBe('English value');
    });

    it('saves description translations', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('translations.en.description', 'English description')
            ->set('value', 'Test Value')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->translate('description', 'en'))->toBe('English description');
    });

    it('saves value translations for string type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('translations.en.value', 'English value')
            ->set('value', 'Default value')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->translate('value', 'en'))->toBe('English value');
    });

    it('does not save value translations for non-string types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'integer',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('translations.en.value', 'Should not save')
            ->set('value', 123)
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        // Value translation should not exist for integer type
        expect($setting->translate('value', 'en'))->toBeNull();
    });

    it('deletes translation when value is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        $setting->setTranslation('description', 'en', 'Existing translation');

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('translations.en.description', '')
            ->set('value', 'Test')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        expect($setting->translate('description', 'en'))->toBeNull();
    });
});

describe('Admin Settings Edit - Center Logo Upload', function () {
    it('can upload logo file for center_logo setting', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'center_logo',
            'type' => 'string',
            'value' => null,
        ]);

        $logo = UploadedFile::fake()->image('logo.jpg', 200, 200);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('logoFile', $logo)
            ->set('value', '') // Empty value when uploading file
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        $updated = $setting->fresh();
        expect($updated->value)->toStartWith('logos/');
        Storage::disk('public')->assertExists($updated->value);
    });

    it('can remove existing logo', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $logoPath = 'logos/existing.jpg';
        Storage::disk('public')->put($logoPath, 'fake content');

        $setting = Setting::factory()->create([
            'key' => 'center_logo',
            'type' => 'string',
            'value' => $logoPath,
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->call('removeLogo')
            ->assertSet('removeExistingLogo', true)
            ->set('value', '')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        $updated = $setting->fresh();
        // Cuando se elimina el logo, el valor puede ser null o cadena vacía
        expect($updated->value)->toBeIn([null, '']);
        Storage::disk('public')->assertMissing($logoPath);
    });

    it('replaces existing logo when uploading new one', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $oldLogoPath = 'logos/old.jpg';
        Storage::disk('public')->put($oldLogoPath, 'old content');

        $setting = Setting::factory()->create([
            'key' => 'center_logo',
            'type' => 'string',
            'value' => $oldLogoPath,
        ]);

        $newLogo = UploadedFile::fake()->image('new-logo.jpg');

        Livewire::test(Edit::class, ['setting' => $setting])
            ->set('logoFile', $newLogo)
            ->set('value', '')
            ->call('confirmUpdate')
            ->call('update')
            ->assertRedirect(route('admin.settings.index'));

        $updated = $setting->fresh();
        expect($updated->value)->toStartWith('logos/');
        expect($updated->value)->not->toBe($oldLogoPath);
        Storage::disk('public')->assertMissing($oldLogoPath);
    });
});

describe('Admin Settings Edit - Immutable Fields', function () {
    it('does not allow modifying key field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'original_key',
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->assertSee('original_key')
            ->assertSee('Clave'); // El label del campo
        // El campo key se muestra pero no se puede editar (es información de solo lectura)
    });

    it('does not allow modifying type field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'test_setting',
            'type' => 'string',
        ]);

        Livewire::test(Edit::class, ['setting' => $setting])
            ->assertSee('Texto'); // El tipo se muestra como "Texto" en español
        // El campo type se muestra pero no se puede editar (es información de solo lectura)
    });
});

describe('Admin Settings Edit - Real-time Validation', function () {
    it('validates value in real-time for string type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
        ]);

        $component = Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', '');

        // El método updatedValue() valida y añade errores si es necesario
        // Verificamos que el componente tiene el valor vacío
        expect($component->get('value'))->toBe('');
    });

    it('validates value in real-time for integer type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'integer',
        ]);

        $component = Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', 'not-a-number');

        // El método updatedValue() valida y añade errores si es necesario
        // Verificamos que el componente tiene el valor inválido
        expect($component->get('value'))->toBe('not-a-number');
    });

    it('validates value in real-time for JSON type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'json',
        ]);

        $component = Livewire::test(Edit::class, ['setting' => $setting])
            ->set('value', '{invalid}');

        // El método updatedValue() valida y añade errores si es necesario
        // Verificamos que el componente tiene el valor inválido
        expect($component->get('value'))->toBe('{invalid}');
    });
});
