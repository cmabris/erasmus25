<?php

use App\Livewire\Admin\Settings\Index;
use App\Models\Setting;
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
    ]);
    $viewer->givePermissionTo([
        Permissions::SETTINGS_VIEW,
    ]);
});

describe('Admin Settings Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.settings.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with SETTINGS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.settings.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('denies access for users without SETTINGS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $this->get(route('admin.settings.index'))
            ->assertForbidden();
    });
});

describe('Admin Settings Index - Listing', function () {
    it('displays all settings grouped by group', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        // Crear configuraciones de diferentes grupos
        Setting::factory()->create(['key' => 'site_name', 'group' => 'general', 'type' => 'string']);
        Setting::factory()->create(['key' => 'contact_email', 'group' => 'email', 'type' => 'string']);
        Setting::factory()->create(['key' => 'rgpd_enabled', 'group' => 'rgpd', 'type' => 'boolean']);

        Livewire::test(Index::class)
            ->assertSuccessful()
            ->assertSee('site_name')
            ->assertSee('contact_email')
            ->assertSee('rgpd_enabled');
    });

    it('groups settings by group correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'setting1', 'group' => 'general']);
        Setting::factory()->create(['key' => 'setting2', 'group' => 'general']);
        Setting::factory()->create(['key' => 'setting3', 'group' => 'email']);

        $component = Livewire::test(Index::class);
        $settings = $component->get('settings');

        expect($settings)->toHaveKey('general');
        expect($settings)->toHaveKey('email');
        expect($settings['general'])->toHaveCount(2);
        expect($settings['email'])->toHaveCount(1);
    });
});

describe('Admin Settings Index - Search', function () {
    it('filters settings by search query in key', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'site_name', 'value' => 'Test Site']);
        Setting::factory()->create(['key' => 'contact_email', 'value' => 'test@example.com']);

        Livewire::test(Index::class)
            ->set('search', 'site')
            ->assertSee('site_name')
            ->assertDontSee('contact_email');
    });

    it('filters settings by search query in value', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'setting1', 'value' => 'Test Value']);
        Setting::factory()->create(['key' => 'setting2', 'value' => 'Other Value']);

        Livewire::test(Index::class)
            ->set('search', 'Test')
            ->assertSee('setting1')
            ->assertDontSee('setting2');
    });

    it('filters settings by search query in description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'setting1', 'description' => 'Test description']);
        Setting::factory()->create(['key' => 'setting2', 'description' => 'Other description']);

        Livewire::test(Index::class)
            ->set('search', 'Test')
            ->assertSee('setting1')
            ->assertDontSee('setting2');
    });
});

describe('Admin Settings Index - Filter by Group', function () {
    it('filters settings by group', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'setting1', 'group' => 'general']);
        Setting::factory()->create(['key' => 'setting2', 'group' => 'email']);

        Livewire::test(Index::class)
            ->set('filterGroup', 'general')
            ->assertSee('setting1')
            ->assertDontSee('setting2');
    });

    it('shows all groups when filter is empty', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'setting1', 'group' => 'general']);
        Setting::factory()->create(['key' => 'setting2', 'group' => 'email']);

        Livewire::test(Index::class)
            ->set('filterGroup', '')
            ->assertSee('setting1')
            ->assertSee('setting2');
    });
});

describe('Admin Settings Index - Sorting', function () {
    it('sorts settings by group ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'z_setting', 'group' => 'seo']);
        Setting::factory()->create(['key' => 'a_setting', 'group' => 'general']);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'group')
            ->set('sortDirection', 'asc');

        $settings = $component->get('settings');
        $groups = array_keys($settings->toArray());

        expect($groups[0])->toBe('general');
        expect($groups[1])->toBe('seo');
    });

    it('sorts settings by group descending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['key' => 'a_setting', 'group' => 'general']);
        Setting::factory()->create(['key' => 'z_setting', 'group' => 'seo']);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'group')
            ->set('sortDirection', 'desc');

        $settings = $component->get('settings');
        $groups = array_keys($settings->toArray());

        expect($groups[0])->toBe('seo');
        expect($groups[1])->toBe('general');
    });

    it('toggles sort direction when clicking same field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'group')
            ->set('sortDirection', 'asc');

        $component->call('sortBy', 'group')
            ->assertSet('sortDirection', 'desc');

        $component->call('sortBy', 'group')
            ->assertSet('sortDirection', 'asc');
    });

    it('sets sort direction to asc when clicking different field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class)
            ->set('sortField', 'group')
            ->set('sortDirection', 'desc');

        $component->call('sortBy', 'key')
            ->assertSet('sortField', 'key')
            ->assertSet('sortDirection', 'asc');
    });
});

describe('Admin Settings Index - Value Formatting', function () {
    it('formats boolean values correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'test_bool',
            'type' => 'boolean',
            'value' => '1',
        ]);

        $component = Livewire::test(Index::class);
        $formatted = $component->instance()->formatValue($setting);

        expect($formatted)->toBeIn(['Sí', 'Yes']); // Depende del idioma
    });

    it('formats integer values correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'test_int',
            'type' => 'integer',
            'value' => '1234',
        ]);

        $component = Livewire::test(Index::class);
        $formatted = $component->instance()->formatValue($setting);

        expect($formatted)->toContain('1.234'); // Formato con separador de miles
    });

    it('formats JSON values correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'key' => 'test_json',
            'type' => 'json',
            'value' => json_encode(['key' => 'value', 'number' => 123]),
        ]);

        $component = Livewire::test(Index::class);
        $formatted = $component->instance()->formatValue($setting);

        expect($formatted)->toContain('JSON');
        expect($formatted)->toContain('2'); // Número de elementos
    });

    it('truncates long string values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $longValue = str_repeat('a', 150);
        $setting = Setting::factory()->create([
            'key' => 'test_string',
            'type' => 'string',
            'value' => $longValue,
        ]);

        $component = Livewire::test(Index::class);
        $formatted = $component->instance()->formatValue($setting);

        expect($formatted)->toHaveLength(103); // 100 caracteres + '...'
        expect($formatted)->toEndWith('...');
    });
});

describe('Admin Settings Index - Reset Filters', function () {
    it('resets search and filter group', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('filterGroup', 'general')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('filterGroup', '');
    });
});

describe('Admin Settings Index - Available Groups', function () {
    it('returns all available groups', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Setting::factory()->create(['group' => 'general']);
        Setting::factory()->create(['group' => 'email']);
        Setting::factory()->create(['group' => 'general']); // Duplicado

        $component = Livewire::test(Index::class);
        $groups = $component->get('availableGroups');

        expect($groups)->toContain('general');
        expect($groups)->toContain('email');
        expect($groups)->toHaveCount(2); // Sin duplicados
    });
});

describe('Admin Settings Index - Helper Methods', function () {
    it('returns correct group label', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);

        expect($component->instance()->getGroupLabel('general'))->toBe('General');
        expect($component->instance()->getGroupLabel('email'))->toBe('Email');
        expect($component->instance()->getGroupLabel('rgpd'))->toBe('RGPD');
    });

    it('returns correct type label', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Index::class);

        expect($component->instance()->getTypeLabel('string'))->toBe('Texto');
        expect($component->instance()->getTypeLabel('integer'))->toBe('Número');
        expect($component->instance()->getTypeLabel('boolean'))->toBe('Booleano');
        expect($component->instance()->getTypeLabel('json'))->toBe('JSON');
    });

    it('checks if value is truncated correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $shortSetting = Setting::factory()->create([
            'type' => 'string',
            'value' => 'short',
        ]);

        $longSetting = Setting::factory()->create([
            'type' => 'string',
            'value' => str_repeat('a', 150),
        ]);

        $component = Livewire::test(Index::class);

        expect($component->instance()->isValueTruncated($shortSetting))->toBeFalse();
        expect($component->instance()->isValueTruncated($longSetting))->toBeTrue();
    });

    it('returns full value for tooltip', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $setting = Setting::factory()->create([
            'type' => 'string',
            'value' => 'Test Value',
        ]);

        $component = Livewire::test(Index::class);
        $fullValue = $component->instance()->getFullValue($setting);

        expect($fullValue)->toBe('Test Value');
    });
});
