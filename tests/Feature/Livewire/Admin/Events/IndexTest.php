<?php

use App\Livewire\Admin\Events\Index;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
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
    Permission::firstOrCreate(['name' => Permissions::EVENTS_VIEW, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_CREATE, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_EDIT, 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => Permissions::EVENTS_DELETE, 'guard_name' => 'web']);

    // Crear roles
    $superAdmin = Role::firstOrCreate(['name' => Roles::SUPER_ADMIN, 'guard_name' => 'web']);
    $admin = Role::firstOrCreate(['name' => Roles::ADMIN, 'guard_name' => 'web']);
    $editor = Role::firstOrCreate(['name' => Roles::EDITOR, 'guard_name' => 'web']);
    $viewer = Role::firstOrCreate(['name' => Roles::VIEWER, 'guard_name' => 'web']);

    // Super-admin tiene todos los permisos
    $superAdmin->givePermissionTo(Permission::all());

    // Admin tiene todos los permisos
    $admin->givePermissionTo([
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
        Permissions::EVENTS_DELETE,
    ]);

    // Editor puede ver, crear y editar pero no eliminar
    $editor->givePermissionTo([
        Permissions::EVENTS_VIEW,
        Permissions::EVENTS_CREATE,
        Permissions::EVENTS_EDIT,
    ]);

    // Viewer solo puede ver
    $viewer->givePermissionTo([
        Permissions::EVENTS_VIEW,
    ]);
});

describe('Admin Events Index - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $this->get(route('admin.events.index'))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with view permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $this->get(route('admin.events.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });

    it('allows admin users to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $this->get(route('admin.events.index'))
            ->assertSuccessful()
            ->assertSeeLivewire(Index::class);
    });
});

describe('Admin Events Index - Listing', function () {
    it('displays all events by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento 1',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento 2',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Evento 1')
            ->assertSee('Evento 2');
    });

    it('displays event information correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'title' => 'Convocatoria Test',
        ]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Test',
            'event_type' => 'apertura',
            'location' => 'Sala A',
        ]);

        Livewire::test(Index::class)
            ->assertSee('Evento Test')
            ->assertSee('Programa Test')
            ->assertSee('Convocatoria Test');
    });

    it('hides deleted events by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Activo',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Eliminado',
        ]);
        $event2->delete();

        Livewire::test(Index::class)
            ->assertSee('Evento Activo')
            ->assertDontSee('Evento Eliminado');
    });

    it('shows deleted events when filter is enabled', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Eliminado',
        ]);
        $event->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->assertSee('Evento Eliminado');
    });
});

describe('Admin Events Index - Filtering', function () {
    it('filters events by program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa 1']);
        $program2 = Program::factory()->create(['name' => 'Programa 2']);
        $call1 = Call::factory()->create(['program_id' => $program1->id]);
        $call2 = Call::factory()->create(['program_id' => $program2->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
            'call_id' => $call1->id,
            'title' => 'Evento Programa 1',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program2->id,
            'call_id' => $call2->id,
            'title' => 'Evento Programa 2',
        ]);

        Livewire::test(Index::class)
            ->set('programFilter', $program1->id)
            ->assertSee('Evento Programa 1')
            ->assertDontSee('Evento Programa 2');
    });

    it('filters events by call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program->id, 'title' => 'Call 1']);
        $call2 = Call::factory()->create(['program_id' => $program->id, 'title' => 'Call 2']);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call1->id,
            'title' => 'Evento Call 1',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call2->id,
            'title' => 'Evento Call 2',
        ]);

        Livewire::test(Index::class)
            ->set('callFilter', $call1->id)
            ->assertSee('Evento Call 1')
            ->assertDontSee('Evento Call 2');
    });

    it('filters events by event type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'event_type' => 'apertura',
            'title' => 'Evento Apertura',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'event_type' => 'cierre',
            'title' => 'Evento Cierre',
        ]);

        Livewire::test(Index::class)
            ->set('eventTypeFilter', 'apertura')
            ->assertSee('Evento Apertura')
            ->assertDontSee('Evento Cierre');
    });

    it('filters events by date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $date1 = now()->addDays(5)->format('Y-m-d');
        $date2 = now()->addDays(10)->format('Y-m-d');

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $date1,
            'title' => 'Evento Día 5',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $date2,
            'title' => 'Evento Día 10',
        ]);

        Livewire::test(Index::class)
            ->set('dateFilter', $date1)
            ->assertSee('Evento Día 5')
            ->assertDontSee('Evento Día 10');
    });

    it('searches events by title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Erasmus',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Movilidad',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'Erasmus')
            ->assertSee('Evento Erasmus')
            ->assertDontSee('Evento Movilidad');
    });

    it('searches events by description', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento 1',
            'description' => 'Descripción con palabra clave',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento 2',
            'description' => 'Otra descripción',
        ]);

        Livewire::test(Index::class)
            ->set('search', 'palabra clave')
            ->assertSee('Evento 1')
            ->assertDontSee('Evento 2');
    });

    it('resets call filter when program filter changes', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program1->id]);

        Livewire::test(Index::class)
            ->set('programFilter', $program1->id)
            ->set('callFilter', $call1->id)
            ->set('programFilter', $program2->id)
            ->assertSet('callFilter', null);
    });
});

describe('Admin Events Index - Sorting', function () {
    it('sorts events by start_date descending by default', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Antiguo',
            'start_date' => now()->subDays(2),
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Nuevo',
            'start_date' => now()->addDays(2),
        ]);

        Livewire::test(Index::class)
            ->assertSeeInOrder(['Evento Nuevo', 'Evento Antiguo']);
    });

    it('sorts events by title ascending', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Z Evento',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'A Evento',
        ]);

        Livewire::test(Index::class)
            ->call('sortBy', 'title')
            ->assertSeeInOrder(['A Evento', 'Z Evento']);
    });

    it('toggles sort direction when clicking same field', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'A Evento',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Z Evento',
        ]);

        $component = Livewire::test(Index::class)
            ->call('sortBy', 'title')
            ->assertSeeInOrder(['A Evento', 'Z Evento']);

        $component->call('sortBy', 'title')
            ->assertSeeInOrder(['Z Evento', 'A Evento']);
    });
});

describe('Admin Events Index - Actions', function () {
    it('can delete an event (soft delete)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Index::class)
            ->set('eventToDelete', $event->id)
            ->call('delete')
            ->assertDispatched('event-deleted');

        expect($event->fresh()->trashed())->toBeTrue();
    });

    it('can restore a deleted event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);
        $event->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->set('eventToRestore', $event->id)
            ->call('restore')
            ->assertDispatched('event-restored');

        expect($event->fresh()->trashed())->toBeFalse();
    });

    it('can force delete an event (permanent deletion)', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);
        $event->delete();

        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->set('eventToForceDelete', $event->id)
            ->call('forceDelete')
            ->assertDispatched('event-force-deleted');

        expect(ErasmusEvent::withTrashed()->find($event->id))->toBeNull();
    });

    it('prevents deletion without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::EDITOR);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Index::class)
            ->set('eventToDelete', $event->id)
            ->call('delete')
            ->assertForbidden();

        expect($event->fresh()->trashed())->toBeFalse();
    });
});

describe('Admin Events Index - Calendar View', function () {
    it('displays calendar view when view mode is calendar', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now(),
        ]);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->assertSet('viewMode', 'calendar');
    });

    it('displays month view by default in calendar mode', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now(),
        ]);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->assertSet('calendarView', 'month');
    });

    it('filters events by month in month view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $currentMonth = now()->startOfMonth();
        $nextMonth = now()->addMonth()->startOfMonth();

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $currentMonth->copy()->addDays(5),
            'title' => 'Evento Mes Actual',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $nextMonth->copy()->addDays(5),
            'title' => 'Evento Mes Siguiente',
        ]);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', $currentMonth->format('Y-m-d'))
            ->assertSee('Evento Mes Actual')
            ->assertDontSee('Evento Mes Siguiente');
    });

    it('filters events by week in week view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $startOfWeek = now()->startOfWeek();
        $nextWeek = now()->addWeek()->startOfWeek();

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $startOfWeek->copy()->addDays(2),
            'title' => 'Evento Semana Actual',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $nextWeek->copy()->addDays(2),
            'title' => 'Evento Semana Siguiente',
        ]);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'week')
            ->set('currentDate', $startOfWeek->format('Y-m-d'))
            ->assertSee('Evento Semana Actual')
            ->assertDontSee('Evento Semana Siguiente');
    });

    it('filters events by day in day view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $today = now();
        $tomorrow = now()->addDay();

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $today,
            'title' => 'Evento Hoy',
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => $tomorrow,
            'title' => 'Evento Mañana',
        ]);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'day')
            ->set('currentDate', $today->format('Y-m-d'))
            ->assertSee('Evento Hoy')
            ->assertDontSee('Evento Mañana');
    });
});

describe('Admin Events Index - Calendar Navigation', function () {
    it('navigates to previous month', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $previousMonth = $currentDate->copy()->subMonth();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('previousMonth')
            ->assertSet('currentDate', $previousMonth->format('Y-m-d'));
    });

    it('navigates to next month', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $nextMonth = $currentDate->copy()->addMonth();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('nextMonth')
            ->assertSet('currentDate', $nextMonth->format('Y-m-d'));
    });

    it('navigates to previous week', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $previousWeek = $currentDate->copy()->subWeek();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'week')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('previousWeek')
            ->assertSet('currentDate', $previousWeek->format('Y-m-d'));
    });

    it('navigates to next week', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $nextWeek = $currentDate->copy()->addWeek();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'week')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('nextWeek')
            ->assertSet('currentDate', $nextWeek->format('Y-m-d'));
    });

    it('navigates to previous day', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $previousDay = $currentDate->copy()->subDay();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'day')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('previousDay')
            ->assertSet('currentDate', $previousDay->format('Y-m-d'));
    });

    it('navigates to next day', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $nextDay = $currentDate->copy()->addDay();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'day')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('nextDay')
            ->assertSet('currentDate', $nextDay->format('Y-m-d'));
    });

    it('goes to today', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', now()->addMonth()->format('Y-m-d'))
            ->call('goToToday')
            ->assertSet('currentDate', now()->format('Y-m-d'));
    });

    it('changes calendar view mode', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->call('changeCalendarView', 'week')
            ->assertSet('calendarView', 'week')
            ->call('changeCalendarView', 'day')
            ->assertSet('calendarView', 'day')
            ->call('changeCalendarView', 'month')
            ->assertSet('calendarView', 'month');
    });

    it('changes view mode between list and calendar', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        Livewire::test(Index::class)
            ->assertSet('viewMode', 'list')
            ->call('changeViewMode', 'calendar')
            ->assertSet('viewMode', 'calendar')
            ->call('changeViewMode', 'list')
            ->assertSet('viewMode', 'list');
    });

    it('uses previous() method for month view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $previousMonth = $currentDate->copy()->subMonth();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'month')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('previous')
            ->assertSet('currentDate', $previousMonth->format('Y-m-d'));
    });

    it('uses next() method for week view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $nextWeek = $currentDate->copy()->addWeek();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'week')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('next')
            ->assertSet('currentDate', $nextWeek->format('Y-m-d'));
    });

    it('uses next() method for day view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $currentDate = now();
        $nextDay = $currentDate->copy()->addDay();

        Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'day')
            ->set('currentDate', $currentDate->format('Y-m-d'))
            ->call('next')
            ->assertSet('currentDate', $nextDay->format('Y-m-d'));
    });
});

describe('Admin Events Index - Pagination', function () {
    it('paginates events correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        // Create more events than per page default (15)
        ErasmusEvent::factory()->count(20)->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Index::class)
            ->assertSee('Evento')
            ->call('$refresh'); // Trigger pagination

        // Verify pagination is working
        $component = Livewire::test(Index::class);
        expect($component->get('events')->count())->toBeLessThanOrEqual(15);
    });

    it('changes per page count', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        ErasmusEvent::factory()->count(25)->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Index::class)
            ->set('perPage', 25)
            ->assertSet('perPage', 25);
    });
});

describe('Admin Events Index - Reset Filters', function () {
    it('resets all filters to default values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        Livewire::test(Index::class)
            ->set('search', 'test')
            ->set('programFilter', $program->id)
            ->set('callFilter', $call->id)
            ->set('eventTypeFilter', 'apertura')
            ->set('dateFilter', now()->format('Y-m-d'))
            ->set('showDeleted', '1')
            ->call('resetFilters')
            ->assertSet('search', '')
            ->assertSet('programFilter', null)
            ->assertSet('callFilter', null)
            ->assertSet('eventTypeFilter', '')
            ->assertSet('dateFilter', '')
            ->assertSet('showDeleted', '0');
    });
});
