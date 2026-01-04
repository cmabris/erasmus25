<?php

use App\Livewire\Admin\Events\Create;
use App\Livewire\Admin\Events\Edit;
use App\Livewire\Admin\Events\Index;
use App\Livewire\Admin\Events\Show;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
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
    Storage::fake('public');

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

describe('Admin Events Integration - Complete Workflow', function () {
    it('can create, edit, delete, and restore an event in a complete workflow', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        // Step 1: Create event
        $image = UploadedFile::fake()->image('event.jpg', 800, 600);

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('call_id', $call->id)
            ->set('title', 'Evento Integración')
            ->set('description', 'Descripción inicial')
            ->set('event_type', 'apertura')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('location', 'Sala A')
            ->set('is_public', true)
            ->set('images', [$image])
            ->call('store')
            ->assertDispatched('event-created')
            ->assertRedirect(route('admin.events.index'));

        $event = ErasmusEvent::where('title', 'Evento Integración')->first();
        expect($event)->not->toBeNull();
        expect($event->description)->toBe('Descripción inicial');
        expect($event->getMedia('images')->count())->toBe(1);

        // Step 2: Edit event
        $newStartDate = now()->addDays(10)->format('Y-m-d\TH:i');
        $newEndDate = now()->addDays(10)->addHours(3)->format('Y-m-d\TH:i');
        $newImage = UploadedFile::fake()->image('event2.png', 800, 600);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', 'Evento Integración Actualizado')
            ->set('description', 'Descripción actualizada')
            ->set('start_date', $newStartDate)
            ->set('end_date', $newEndDate)
            ->set('location', 'Sala B')
            ->set('is_public', false)
            ->set('images', [$newImage])
            ->call('update')
            ->assertDispatched('event-updated')
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();
        expect($event->title)->toBe('Evento Integración Actualizado');
        expect($event->description)->toBe('Descripción actualizada');
        expect($event->location)->toBe('Sala B');
        expect($event->is_public)->toBeFalse();
        expect($event->getMedia('images')->count())->toBe(2);

        // Step 3: View event in Show
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->assertSee('Evento Integración Actualizado')
            ->assertSee('Descripción actualizada')
            ->assertSee('Sala B');

        // Step 4: Delete event (soft delete)
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->call('confirmDelete')
            ->call('delete')
            ->assertDispatched('event-deleted')
            ->assertRedirect(route('admin.events.index'));

        expect($event->fresh()->trashed())->toBeTrue();

        // Step 5: Verify event is hidden in index by default
        Livewire::test(Index::class)
            ->assertDontSee('Evento Integración Actualizado');

        // Step 6: Show deleted events
        Livewire::test(Index::class)
            ->set('showDeleted', '1')
            ->assertSee('Evento Integración Actualizado');

        // Step 7: Restore event
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->call('confirmRestore')
            ->call('restore')
            ->assertDispatched('event-restored');

        expect($event->fresh()->trashed())->toBeFalse();

        // Step 8: Verify event is visible again in index
        Livewire::test(Index::class)
            ->assertSee('Evento Integración Actualizado');
    });

    it('can manage images throughout the complete workflow', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        // Create event with images
        $image1 = UploadedFile::fake()->image('event1.jpg', 800, 600);
        $image2 = UploadedFile::fake()->image('event2.png', 800, 600);

        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('call_id', $call->id)
            ->set('title', 'Evento Con Imágenes')
            ->set('event_type', 'apertura')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('images', [$image1, $image2])
            ->call('store');

        $event = ErasmusEvent::where('title', 'Evento Con Imágenes')->first();
        expect($event->getMedia('images')->count())->toBe(2);

        $media1 = $event->getMedia('images')->first();

        // Edit: soft delete one image
        Livewire::test(Edit::class, ['event' => $event])
            ->call('confirmDeleteImage', $media1->id)
            ->call('deleteImage')
            ->assertDispatched('image-deleted');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(1);
        expect($event->hasSoftDeletedImages())->toBeTrue();

        // Edit: restore deleted image
        Livewire::test(Edit::class, ['event' => $event])
            ->call('restoreImage', $media1->id)
            ->assertDispatched('image-restored');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(2);
        expect($event->hasSoftDeletedImages())->toBeFalse();

        // Edit: add new image
        $image3 = UploadedFile::fake()->image('event3.jpg', 800, 600);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('images', [$image3])
            ->call('update');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(3);

        // Show: verify all images are displayed
        $component = Livewire::test(Show::class, ['event' => $event->load('media')]);
        expect($component->get('images')->count())->toBe(3);
        expect($component->get('hasImages'))->toBeTrue();
    });
});

describe('Admin Events Integration - Association with Calls', function () {
    it('can create event linked to call and navigate through all views', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create(['name' => 'Programa Test']);
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'title' => 'Convocatoria Test',
        ]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        // Create event from call context
        Livewire::test(Create::class, ['program_id' => $program->id, 'call_id' => $call->id])
            ->assertSet('program_id', $program->id)
            ->assertSet('call_id', $call->id)
            ->set('title', 'Evento Desde Convocatoria')
            ->set('event_type', 'apertura')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->call('store');

        $event = ErasmusEvent::where('title', 'Evento Desde Convocatoria')->first();
        expect($event->program_id)->toBe($program->id);
        expect($event->call_id)->toBe($call->id);

        // View in Index with filters
        Livewire::test(Index::class)
            ->set('programFilter', $program->id)
            ->set('callFilter', $call->id)
            ->assertSee('Evento Desde Convocatoria');

        // View in Show
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->assertSee('Programa Test')
            ->assertSee('Convocatoria Test');

        // Edit and change association
        $program2 = Program::factory()->create();
        $call2 = Call::factory()->create(['program_id' => $program2->id]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('program_id', $program2->id)
            ->set('call_id', $call2->id)
            ->call('update');

        $event->refresh();
        expect($event->program_id)->toBe($program2->id);
        expect($event->call_id)->toBe($call2->id);
    });

    it('maintains call association when filtering in index', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program->id, 'title' => 'Call 1']);
        $call2 = Call::factory()->create(['program_id' => $program->id, 'title' => 'Call 2']);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call1->id,
            'title' => 'Evento Call 1',
            'start_date' => $startDate,
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call2->id,
            'title' => 'Evento Call 2',
            'start_date' => $startDate,
        ]);

        // Filter by call1
        Livewire::test(Index::class)
            ->set('callFilter', $call1->id)
            ->assertSee('Evento Call 1')
            ->assertDontSee('Evento Call 2');

        // Filter by call2
        Livewire::test(Index::class)
            ->set('callFilter', $call2->id)
            ->assertSee('Evento Call 2')
            ->assertDontSee('Evento Call 1');
    });
});

describe('Admin Events Integration - Combined Filters', function () {
    it('can combine multiple filters in index view', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create(['name' => 'Programa 1']);
        $program2 = Program::factory()->create(['name' => 'Programa 2']);
        $call1 = Call::factory()->create(['program_id' => $program1->id]);
        $call2 = Call::factory()->create(['program_id' => $program2->id]);

        $date1 = now()->addDays(5)->format('Y-m-d');
        $date2 = now()->addDays(10)->format('Y-m-d');

        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
            'call_id' => $call1->id,
            'title' => 'Evento Filtrado 1',
            'event_type' => 'apertura',
            'start_date' => $date1,
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program2->id,
            'call_id' => $call2->id,
            'title' => 'Evento Filtrado 2',
            'event_type' => 'cierre',
            'start_date' => $date2,
        ]);

        // Combine program, type, and date filters
        Livewire::test(Index::class)
            ->set('programFilter', $program1->id)
            ->set('eventTypeFilter', 'apertura')
            ->set('dateFilter', $date1)
            ->assertSee('Evento Filtrado 1')
            ->assertDontSee('Evento Filtrado 2');

        // Combine search and type filter
        Livewire::test(Index::class)
            ->set('search', 'Filtrado 2')
            ->set('eventTypeFilter', 'cierre')
            ->assertSee('Evento Filtrado 2')
            ->assertDontSee('Evento Filtrado 1');

        // Reset filters
        Livewire::test(Index::class)
            ->set('programFilter', $program1->id)
            ->set('eventTypeFilter', 'apertura')
            ->set('dateFilter', $date1)
            ->call('resetFilters')
            ->assertSet('programFilter', null)
            ->assertSet('eventTypeFilter', '')
            ->assertSet('dateFilter', '');
    });
});

describe('Admin Events Integration - Calendar with Multiple Events', function () {
    it('displays multiple events correctly in calendar views', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $currentMonth = now()->startOfMonth();
        $nextMonth = now()->addMonth()->startOfMonth();

        // Create events in current month
        $event1 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Mes Actual 1',
            'start_date' => $currentMonth->copy()->addDays(5),
        ]);

        $event2 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Mes Actual 2',
            'start_date' => $currentMonth->copy()->addDays(10),
        ]);

        // Create event in next month
        $event3 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Mes Siguiente',
            'start_date' => $nextMonth->copy()->addDays(5),
        ]);

        // Month view - current month
        $component = Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', $currentMonth->format('Y-m-d'));

        $calendarEvents = $component->get('calendarEvents');
        $eventTitles = $calendarEvents->pluck('title')->toArray();
        expect($eventTitles)->toContain('Evento Mes Actual 1');
        expect($eventTitles)->toContain('Evento Mes Actual 2');
        expect($eventTitles)->not->toContain('Evento Mes Siguiente');

        // Navigate to next month - need to refresh the component to get updated events
        $nextMonthComponent = Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('currentDate', $nextMonth->format('Y-m-d'));

        $calendarEvents = $nextMonthComponent->get('calendarEvents');
        $eventTitles = $calendarEvents->pluck('title')->toArray();
        expect($eventTitles)->toContain('Evento Mes Siguiente');
        expect($eventTitles)->not->toContain('Evento Mes Actual 1');

        // Week view
        $startOfWeek = now()->startOfWeek();
        $event4 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Semana Actual',
            'start_date' => $startOfWeek->copy()->addDays(2),
        ]);

        $component = Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'week')
            ->set('currentDate', $startOfWeek->format('Y-m-d'));

        $calendarEvents = $component->get('calendarEvents');
        $eventTitles = $calendarEvents->pluck('title')->toArray();
        expect($eventTitles)->toContain('Evento Semana Actual');

        // Day view
        $today = now();
        $event5 = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Hoy',
            'start_date' => $today,
        ]);

        $component = Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('calendarView', 'day')
            ->set('currentDate', $today->format('Y-m-d'));

        $dayEvents = $component->get('dayEvents');
        $eventTitles = $dayEvents->pluck('title')->toArray();
        expect($eventTitles)->toContain('Evento Hoy');
    });

    it('can navigate calendar and maintain filters', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $currentMonth = now()->startOfMonth();

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Filtrado',
            'event_type' => 'apertura',
            'start_date' => $currentMonth->copy()->addDays(5),
        ]);

        // Set filters and navigate calendar
        $component = Livewire::test(Index::class)
            ->set('viewMode', 'calendar')
            ->set('programFilter', $program->id)
            ->set('eventTypeFilter', 'apertura')
            ->set('currentDate', $currentMonth->format('Y-m-d'));

        $calendarEvents = $component->get('calendarEvents');
        $eventTitles = $calendarEvents->pluck('title')->toArray();
        expect($eventTitles)->toContain('Evento Filtrado');

        // Navigate to next month (should maintain filters)
        $component->call('nextMonth')
            ->assertSet('programFilter', $program->id)
            ->assertSet('eventTypeFilter', 'apertura');
    });
});

describe('Admin Events Integration - Permissions by Role', function () {
    it('enforces permissions correctly across all components for admin role', function () {
        $admin = User::factory()->create();
        $admin->assignRole(Roles::ADMIN);
        $this->actingAs($admin);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        // Admin can create
        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('call_id', $call->id)
            ->set('title', 'Evento Admin')
            ->set('event_type', 'apertura')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->call('store')
            ->assertDispatched('event-created');

        $event = ErasmusEvent::where('title', 'Evento Admin')->first();

        // Admin can view in index
        Livewire::test(Index::class)
            ->assertSee('Evento Admin');

        // Admin can view in show
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->assertSee('Evento Admin');

        // Admin can edit
        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', 'Evento Admin Editado')
            ->call('update')
            ->assertDispatched('event-updated');

        // Admin can delete
        Livewire::test(Show::class, ['event' => $event->fresh()->load('program', 'call', 'creator')])
            ->call('delete')
            ->assertDispatched('event-deleted');

        expect($event->fresh()->trashed())->toBeTrue();
    });

    it('enforces permissions correctly across all components for editor role', function () {
        $editor = User::factory()->create();
        $editor->assignRole(Roles::EDITOR);
        $this->actingAs($editor);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        // Editor can create
        Livewire::test(Create::class)
            ->set('program_id', $program->id)
            ->set('call_id', $call->id)
            ->set('title', 'Evento Editor')
            ->set('event_type', 'apertura')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->call('store')
            ->assertDispatched('event-created');

        $event = ErasmusEvent::where('title', 'Evento Editor')->first();

        // Editor can view
        Livewire::test(Index::class)
            ->assertSee('Evento Editor');

        // Editor can edit
        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', 'Evento Editor Editado')
            ->call('update')
            ->assertDispatched('event-updated');

        // Editor cannot delete
        Livewire::test(Show::class, ['event' => $event->fresh()->load('program', 'call', 'creator')])
            ->call('delete')
            ->assertForbidden();

        expect($event->fresh()->trashed())->toBeFalse();
    });

    it('enforces permissions correctly across all components for viewer role', function () {
        $viewer = User::factory()->create();
        $viewer->assignRole(Roles::VIEWER);
        $this->actingAs($viewer);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        // Viewer can view in index
        Livewire::test(Index::class)
            ->assertSee($event->title);

        // Viewer can view in show
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->assertSee($event->title);

        // Viewer cannot create
        $this->get(route('admin.events.create'))
            ->assertForbidden();

        // Viewer cannot edit
        $this->get(route('admin.events.edit', $event))
            ->assertForbidden();

        // Viewer cannot delete
        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->call('delete')
            ->assertForbidden();
    });
});
