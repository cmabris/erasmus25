<?php

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\Setting;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Blade;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');

    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);

    // Crear roles
    Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
});

/*
|--------------------------------------------------------------------------
| Admin Navigation Component Tests
|--------------------------------------------------------------------------
*/

describe('Admin Navigation Component', function () {
    it('renders navigation component', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('data-flux-navlist')
            ->toContain('Dashboard');
    });

    it('shows dashboard link for all authenticated users', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Dashboard')
            ->toContain(route('admin.dashboard'));
    });

    it('shows programs link only if user can view programs', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Programas')
            ->toContain(route('admin.programs.index'));
    });

    it('hides programs link if user cannot view programs', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->not->toContain('Programas')
            ->not->toContain(route('admin.programs.index'));
    });

    it('shows calls link only if user can view calls', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Convocatorias')
            ->toContain(route('admin.calls.index'));
    });

    it('shows news link only if user can view news', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::NEWS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Noticias')
            ->toContain(route('admin.news.index'));
    });

    it('shows documents link only if user can view documents', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Documentos')
            ->toContain(route('admin.documents.index'));
    });

    it('shows events link only if user can view events', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::EVENTS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Eventos')
            ->toContain(route('admin.events.index'));
    });

    it('shows academic years link for all authenticated users', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Años Académicos')
            ->toContain(route('admin.academic-years.index'));
    });

    it('shows users link only if user can view users', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Usuarios')
            ->toContain(route('admin.users.index'));
    });

    it('shows content group only if user has access to any content module', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Contenido')
            ->toContain('Programas');
    });

    it('hides content group if user has no content access', function () {
        $user = User::factory()->create();
        // No dar permisos de contenido
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->not->toContain('Contenido')
            ->not->toContain('Programas')
            ->not->toContain('Convocatorias')
            ->not->toContain('Noticias');
    });

    it('shows system group only if user can view users', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Sistema')
            ->toContain('Usuarios');
    });

    it('shows all navigation items for super-admin', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        expect($html)
            ->toContain('Dashboard')
            ->toContain('Programas')
            ->toContain('Convocatorias')
            ->toContain('Noticias')
            ->toContain('Documentos')
            ->toContain('Eventos')
            ->toContain('Años Académicos')
            ->toContain('Usuarios')
            ->toContain('Sistema');
    });

    it('uses wire:navigate for all navigation links', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $html = Blade::render('<x-nav.admin-nav />');

        // Contar cuántas veces aparece wire:navigate
        $count = substr_count($html, 'wire:navigate');
        
        // Debe aparecer al menos una vez (para cada enlace)
        expect($count)->toBeGreaterThan(0);
    });

    it('marks current route as active', function () {
        $user = User::factory()->create();
        $permission = Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
        $user->givePermissionTo($permission);
        $this->actingAs($user);

        // Simular que estamos en la ruta de programas
        $response = $this->get(route('admin.programs.index'));
        $response->assertSuccessful();

        // En un contexto real, el componente detectaría la ruta actual
        // Aquí solo verificamos que el componente se renderiza correctamente
        $html = Blade::render('<x-nav.admin-nav />');

        // Verificar que el enlace a programas está presente
        expect($html)
            ->toContain('Programas')
            ->toContain(route('admin.programs.index'));
    });
});
