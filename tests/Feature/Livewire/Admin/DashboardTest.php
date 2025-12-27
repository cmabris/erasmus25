<?php

use App\Livewire\Admin\Dashboard;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Crear permisos necesarios
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::PROGRAMS_ALL, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::CALLS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::NEWS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::DOCUMENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::USERS_VIEW, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Asignar permisos a roles
    $superAdmin->givePermissionTo(Permission::all());
    $admin->givePermissionTo([
        Permissions::PROGRAMS_ALL,
        Permissions::CALLS_CREATE,
        Permissions::CALLS_VIEW,
        Permissions::NEWS_CREATE,
        Permissions::DOCUMENTS_CREATE,
        Permissions::EVENTS_CREATE,
    ]);
    $editor->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::CALLS_CREATE,
        Permissions::NEWS_CREATE,
        Permissions::DOCUMENTS_CREATE,
        Permissions::EVENTS_CREATE,
    ]);
    $viewer->givePermissionTo([
        Permissions::PROGRAMS_VIEW,
        Permissions::CALLS_VIEW,
    ]);

    // Crear año académico necesario
    AcademicYear::factory()->create([
        'year' => '2024-2025',
        'start_date' => now()->startOfYear(),
        'end_date' => now()->endOfYear(),
    ]);

    // Limpiar caché antes de cada test
    Cache::flush();
});

describe('Admin Dashboard - Access Control', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.dashboard'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with admin permissions to access dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(Dashboard::class);
    });

    it('allows super-admin users to access dashboard', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSuccessful()
            ->assertSeeLivewire(Dashboard::class);
    });
});

describe('Admin Dashboard - Statistics', function () {
    it('displays correct count of active programs', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(3)->create(['is_active' => true]);
        Program::factory()->count(2)->create(['is_active' => false]);

        Livewire::test(Dashboard::class)
            ->assertSet('activePrograms', 3);
    });

    it('displays correct count of open calls', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Call::factory()->count(2)->create([
            'status' => 'abierta',
            'published_at' => now(),
        ]);
        Call::factory()->count(1)->create([
            'status' => 'abierta',
            'published_at' => null,
        ]);

        Livewire::test(Dashboard::class)
            ->assertSet('openCalls', 2);
    });

    it('displays correct count of closed calls', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Call::factory()->count(5)->create(['status' => 'cerrada']);

        Livewire::test(Dashboard::class)
            ->assertSet('closedCalls', 5);
    });

    it('displays correct count of news published this month', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        NewsPost::factory()->count(3)->create([
            'status' => 'publicado',
            'published_at' => now(),
        ]);
        NewsPost::factory()->count(2)->create([
            'status' => 'publicado',
            'published_at' => now()->subMonth(),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSet('newsThisMonth', 3);
    });

    it('displays correct count of available documents', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $category = DocumentCategory::factory()->create();
        Document::factory()->count(4)->create(['is_active' => true, 'category_id' => $category->id]);
        Document::factory()->count(1)->create(['is_active' => false, 'category_id' => $category->id]);

        Livewire::test(Dashboard::class)
            ->assertSet('availableDocuments', 4);
    });

    it('displays correct count of upcoming events', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        ErasmusEvent::factory()->count(3)->create([
            'is_public' => true,
            'start_date' => now()->addDays(5),
        ]);
        ErasmusEvent::factory()->count(2)->create([
            'is_public' => true,
            'start_date' => now()->subDays(5),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSet('upcomingEvents', 3);
    });
});

describe('Admin Dashboard - Permissions', function () {
    it('shows create call action only for users with CALLS_CREATE permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee(__('common.admin.dashboard.quick_actions.create_call'));
    });

    it('hides create call action for users without CALLS_CREATE permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertDontSee(__('common.admin.dashboard.quick_actions.create_call'));
    });

    it('shows manage users action only for super-admin', function () {
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(Roles::SUPER_ADMIN);
        $this->actingAs($superAdmin);

        Livewire::test(Dashboard::class)
            ->assertSee(__('common.admin.dashboard.quick_actions.manage_users'));

        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        Livewire::test(Dashboard::class)
            ->assertDontSee(__('common.admin.dashboard.quick_actions.manage_users'));
    });
});

describe('Admin Dashboard - Recent Activities', function () {
    it('displays recent activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $call = Call::factory()->create([
            'title' => 'Test Call',
            'updated_at' => now(),
        ]);

        Livewire::test(Dashboard::class)
            ->assertSee('Test Call');
    });

    it('shows empty state when there are no activities', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertSee(__('common.admin.dashboard.activity.no_activity'));
    });
});

describe('Admin Dashboard - Alerts', function () {
    it('displays alerts for calls closing soon', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $call = Call::factory()->create([
            'status' => 'abierta',
            'published_at' => now()->subDays(10),
            'closed_at' => now()->addDays(3),
        ]);

        $component = Livewire::test(Dashboard::class);
        $alerts = $component->get('alerts');

        expect($alerts)->not->toBeEmpty()
            ->and($alerts->firstWhere('type', 'call_closing_soon'))->not->toBeNull();
    });

    it('displays alerts for unpublished drafts older than 7 days', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $call = Call::factory()->create([
            'status' => 'borrador',
            'published_at' => null,
            'created_at' => now()->subDays(10),
        ]);

        $component = Livewire::test(Dashboard::class);
        $alerts = $component->get('alerts');

        expect($alerts)->not->toBeEmpty()
            ->and($alerts->firstWhere('type', 'unpublished_draft'))->not->toBeNull();
    });

    it('displays alerts for events without location', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $event = ErasmusEvent::factory()->create([
            'is_public' => true,
            'start_date' => now()->addDays(5),
            'location' => null,
        ]);

        $component = Livewire::test(Dashboard::class);
        $alerts = $component->get('alerts');

        expect($alerts)->not->toBeEmpty()
            ->and($alerts->firstWhere('type', 'event_missing_location'))->not->toBeNull();
    });

    it('does not show alerts when there are none', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $alerts = $component->get('alerts');

        expect($alerts)->toBeEmpty();
    });
});

describe('Admin Dashboard - Charts Data', function () {
    it('returns monthly activity data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $data = $component->instance()->getMonthlyActivityData();

        expect($data)->toHaveKeys(['labels', 'datasets'])
            ->and($data['labels'])->toBeArray()
            ->and($data['datasets'])->toBeArray();
    });

    it('returns calls by status data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $data = $component->instance()->getCallsByStatusData();

        expect($data)->toHaveKeys(['labels', 'data', 'colors'])
            ->and($data['labels'])->toBeArray()
            ->and($data['data'])->toBeArray();
    });

    it('returns calls by program data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['is_active' => true]);
        Call::factory()->count(3)->create([
            'program_id' => $program->id,
            'published_at' => now(),
        ]);

        $component = Livewire::test(Dashboard::class);
        $data = $component->instance()->getCallsByProgramData();

        expect($data)->toHaveKeys(['labels', 'data', 'colors'])
            ->and($data['labels'])->toBeArray()
            ->and($data['data'])->toBeArray();
    });
});

describe('Admin Dashboard - Caching', function () {
    it('caches statistics', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(5)->create(['is_active' => true]);

        // Primera carga - debe ejecutar la consulta
        Livewire::test(Dashboard::class)
            ->assertSet('activePrograms', 5);

        // Verificar que el caché existe
        expect(Cache::has('dashboard.statistics'))->toBeTrue();

        // Crear más programas
        Program::factory()->count(3)->create(['is_active' => true]);

        // Segunda carga - debe usar caché (sigue mostrando 5)
        Livewire::test(Dashboard::class)
            ->assertSet('activePrograms', 5);
    });

    it('can clear dashboard cache', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Program::factory()->count(5)->create(['is_active' => true]);

        // Cargar dashboard para crear caché
        Livewire::test(Dashboard::class);

        // Verificar que el caché existe
        expect(Cache::has('dashboard.statistics'))->toBeTrue();

        // Limpiar caché
        Dashboard::clearCache();

        // Verificar que el caché fue eliminado
        expect(Cache::has('dashboard.statistics'))->toBeFalse();
    });

    it('caches chart data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $component = Livewire::test(Dashboard::class);
        $component->instance()->getMonthlyActivityData();

        // Verificar que el caché existe
        expect(Cache::has('dashboard.charts.monthly_activity'))->toBeTrue();
    });
});

describe('Admin Dashboard - Rendering', function () {
    it('renders dashboard title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSee(__('Dashboard'));
    });

    it('renders statistics section', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSee(__('common.admin.dashboard.statistics_title'));
    });

    it('renders quick actions section', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSee(__('common.admin.dashboard.quick_actions_title'));
    });

    it('renders recent activity section', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSee(__('common.admin.dashboard.recent_activity_title'));
    });

    it('renders charts section', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.dashboard'))
            ->assertSee(__('common.admin.dashboard.charts.monthly_activity_title'));
    });
});
