<?php

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

describe('Admin Events Show - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.show', $event))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with EVENTS_VIEW permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.show', $event))
            ->assertSuccessful()
            ->assertSeeLivewire(Show::class);
    });

    it('denies access for users without EVENTS_VIEW permission', function () {
        $user = User::factory()->create();
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.show', $event))
            ->assertForbidden();
    });
});

describe('Admin Events Show - Display', function () {
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
            'description' => 'Descripción del evento',
            'event_type' => 'apertura',
            'location' => 'Sala A',
            'is_public' => true,
        ]);

        Livewire::test(Show::class, ['event' => $event])
            ->assertSee('Evento Test')
            ->assertSee('Descripción del evento')
            ->assertSee('Programa Test')
            ->assertSee('Convocatoria Test')
            ->assertSee('Sala A');
    });

    it('displays event without program and call correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $event = ErasmusEvent::factory()->create([
            'program_id' => null,
            'call_id' => null,
            'title' => 'Evento Sin Programa',
        ]);

        Livewire::test(Show::class, ['event' => $event])
            ->assertSee('Evento Sin Programa');
    });

    it('displays event creator information', function () {
        $user = User::factory()->create(['name' => 'Usuario Creador']);
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'created_by' => $user->id,
        ]);

        Livewire::test(Show::class, ['event' => $event])
            ->assertSee('Usuario Creador');
    });

    it('displays event images correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $image1 = UploadedFile::fake()->image('event1.jpg', 800, 600);
        $image2 = UploadedFile::fake()->image('event2.png', 800, 600);

        $event->addMedia($image1->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');
        $event->addMedia($image2->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        $component = Livewire::test(Show::class, ['event' => $event->load('media')]);

        $images = $component->get('images');
        expect($images->count())->toBe(2);
        expect($component->get('hasImages'))->toBeTrue();
    });

    it('displays featured image URL correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $image = UploadedFile::fake()->image('event.jpg', 800, 600);
        $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        $component = Livewire::test(Show::class, ['event' => $event->load('media')]);

        $featuredUrl = $component->get('featuredImageUrl');
        expect($featuredUrl)->not->toBeNull();
    });

    it('displays event statistics correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(3),
        ]);

        $image = UploadedFile::fake()->image('event.jpg', 800, 600);
        $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        $component = Livewire::test(Show::class, ['event' => $event->load('media')]);

        $statistics = $component->get('statistics');
        expect($statistics['duration'])->toBe(3.0);
        expect($statistics['images_count'])->toBe(1);
    });

    it('displays event type badge correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'event_type' => 'apertura',
        ]);

        $component = Livewire::test(Show::class, ['event' => $event]);

        $config = $component->instance()->getEventTypeConfig('apertura');
        expect($config['variant'])->toBe('success');
        expect($config['icon'])->toBe('play-circle');
    });

    it('displays event status badge correctly for upcoming event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now()->addDays(5),
        ]);

        $component = Livewire::test(Show::class, ['event' => $event]);

        $config = $component->instance()->getEventStatusConfig();
        expect($config['variant'])->toBe('success');
        expect($config['label'])->toBe(__('Próximo'));
    });

    it('displays event status badge correctly for today event', function () {
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

        $component = Livewire::test(Show::class, ['event' => $event]);

        $config = $component->instance()->getEventStatusConfig();
        expect($config['variant'])->toBe('info');
        expect($config['label'])->toBe(__('Hoy'));
    });

    it('displays event status badge correctly for past event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now()->subDays(5),
        ]);

        $component = Livewire::test(Show::class, ['event' => $event]);

        $config = $component->instance()->getEventStatusConfig();
        expect($config['variant'])->toBe('neutral');
        expect($config['label'])->toBe(__('Pasado'));
    });

    it('displays event status badge correctly for deleted event', function () {
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

        $component = Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')]);

        $config = $component->instance()->getEventStatusConfig();
        expect($config['variant'])->toBe('danger');
        expect($config['label'])->toBe(__('Eliminado'));
    });
});

describe('Admin Events Show - Actions', function () {
    it('can toggle public/private visibility', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'is_public' => true,
        ]);

        Livewire::test(Show::class, ['event' => $event])
            ->call('togglePublic')
            ->assertDispatched('visibility-toggled');

        expect($event->fresh()->is_public)->toBeFalse();
    });

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

        Livewire::test(Show::class, ['event' => $event])
            ->call('confirmDelete')
            ->assertSet('showDeleteModal', true)
            ->call('delete')
            ->assertDispatched('event-deleted')
            ->assertRedirect(route('admin.events.index'));

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

        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->call('confirmRestore')
            ->assertSet('showRestoreModal', true)
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

        Livewire::test(Show::class, ['event' => $event->load('program', 'call', 'creator')])
            ->call('confirmForceDelete')
            ->assertSet('showForceDeleteModal', true)
            ->call('forceDelete')
            ->assertDispatched('event-force-deleted')
            ->assertRedirect(route('admin.events.index'));

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

        Livewire::test(Show::class, ['event' => $event])
            ->call('delete')
            ->assertForbidden();

        expect($event->fresh()->trashed())->toBeFalse();
    });

    it('prevents toggle visibility without permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Show::class, ['event' => $event])
            ->call('togglePublic')
            ->assertForbidden();
    });
});

describe('Admin Events Show - Event Type Config', function () {
    it('returns correct config for all event types', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $event = ErasmusEvent::factory()->create();

        $component = Livewire::test(Show::class, ['event' => $event]);
        $instance = $component->instance();

        // Test all event types
        expect($instance->getEventTypeConfig('apertura'))->toBe(['variant' => 'success', 'icon' => 'play-circle', 'label' => __('Apertura')])
            ->and($instance->getEventTypeConfig('cierre'))->toBe(['variant' => 'danger', 'icon' => 'stop-circle', 'label' => __('Cierre')])
            ->and($instance->getEventTypeConfig('entrevista'))->toBe(['variant' => 'info', 'icon' => 'chat-bubble-left-right', 'label' => __('Entrevistas')])
            ->and($instance->getEventTypeConfig('publicacion_provisional'))->toBe(['variant' => 'warning', 'icon' => 'document-text', 'label' => __('Listado provisional')])
            ->and($instance->getEventTypeConfig('publicacion_definitivo'))->toBe(['variant' => 'success', 'icon' => 'document-check', 'label' => __('Listado definitivo')])
            ->and($instance->getEventTypeConfig('reunion_informativa'))->toBe(['variant' => 'primary', 'icon' => 'user-group', 'label' => __('Reunión informativa')])
            ->and($instance->getEventTypeConfig('unknown_type'))->toBe(['variant' => 'neutral', 'icon' => 'calendar', 'label' => __('Otro')]);
    });
});
