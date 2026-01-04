<?php

use App\Livewire\Admin\Events\Edit;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use App\Support\Permissions;
use App\Support\Roles;
use Carbon\Carbon;
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

describe('Admin Events Edit - Authorization', function () {
    it('redirects unauthenticated users to login', function () {
        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.edit', $event))
            ->assertRedirect('/login');
    });

    it('allows authenticated users with EVENTS_EDIT permission to access', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.edit', $event))
            ->assertSuccessful()
            ->assertSeeLivewire(Edit::class);
    });

    it('denies access for users without EVENTS_EDIT permission', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::VIEWER);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $this->get(route('admin.events.edit', $event))
            ->assertForbidden();
    });
});

describe('Admin Events Edit - Data Loading', function () {
    it('loads existing event data correctly', function () {
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
            'title' => 'Evento Original',
            'description' => 'Descripción original',
            'event_type' => 'apertura',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(5)->addHours(2),
            'location' => 'Sala A',
            'is_public' => true,
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event]);

        expect($component->get('title'))->toBe('Evento Original')
            ->and($component->get('description'))->toBe('Descripción original')
            ->and($component->get('event_type'))->toBe('apertura')
            ->and($component->get('program_id'))->toBe($program->id)
            ->and($component->get('call_id'))->toBe($call->id)
            ->and($component->get('location'))->toBe('Sala A')
            ->and($component->get('is_public'))->toBeTrue();
    });

    it('loads event without end date correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'end_date' => null,
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event]);

        expect($component->get('end_date'))->toBe('');
    });

    it('loads is_all_day correctly based on event times', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        // Event with all day times
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'start_date' => now()->startOfDay(),
            'end_date' => now()->startOfDay(),
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event]);

        expect($component->get('is_all_day'))->toBeTrue();
    });
});

describe('Admin Events Edit - Successful Update', function () {
    it('can update an event with valid data', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'title' => 'Evento Original',
            'event_type' => 'apertura',
        ]);

        $startDate = now()->addDays(10)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(10)->addHours(2)->format('Y-m-d\TH:i');

        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', 'Evento Actualizado')
            ->set('description', 'Nueva descripción')
            ->set('event_type', 'cierre')
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('location', 'Sala B')
            ->set('is_public', false)
            ->call('update')
            ->assertDispatched('event-updated')
            ->assertRedirect(route('admin.events.show', $event));

        $event->refresh();
        expect($event->title)->toBe('Evento Actualizado')
            ->and($event->description)->toBe('Nueva descripción')
            ->and($event->event_type)->toBe('cierre')
            ->and($event->location)->toBe('Sala B')
            ->and($event->is_public)->toBeFalse();
    });

    it('can update event without end date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
            'end_date' => now()->addDays(5)->addHours(2),
        ]);

        $startDate = now()->addDays(10)->format('Y-m-d\TH:i');

        Livewire::test(Edit::class, ['event' => $event])
            ->set('start_date', $startDate)
            ->set('end_date', '')
            ->call('update')
            ->assertDispatched('event-updated');

        expect($event->fresh()->end_date)->toBeNull();
    });

    it('can update event program and call', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program1->id]);
        $call2 = Call::factory()->create(['program_id' => $program2->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
            'call_id' => $call1->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('program_id', $program2->id)
            ->set('call_id', $call2->id)
            ->call('update')
            ->assertDispatched('event-updated');

        $event->refresh();
        expect($event->program_id)->toBe($program2->id)
            ->and($event->call_id)->toBe($call2->id);
    });

    it('can add new images to existing event', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $image1 = UploadedFile::fake()->image('new1.jpg', 800, 600);
        $image2 = UploadedFile::fake()->image('new2.png', 800, 600);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('images', [$image1, $image2])
            ->call('update')
            ->assertDispatched('event-updated');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(2);
    });
});

describe('Admin Events Edit - Validation', function () {
    it('requires title', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', '')
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('validates title max length', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', str_repeat('a', 256))
            ->call('update')
            ->assertHasErrors(['title']);
    });

    it('requires event_type', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('event_type', '')
            ->call('update')
            ->assertHasErrors(['event_type']);
    });

    it('validates event_type is in allowed values', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('event_type', 'invalid_type')
            ->call('update')
            ->assertHasErrors(['event_type']);
    });

    it('validates end_date is after start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(4)->format('Y-m-d\TH:i'); // Before start

        Livewire::test(Edit::class, ['event' => $event])
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->call('update')
            ->assertHasErrors(['end_date']);
    });
});

describe('Admin Events Edit - Real-time Validation', function () {
    it('validates title in real-time', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('title', '')
            ->assertHasErrors(['title'])
            ->set('title', 'Valid Title')
            ->assertHasNoErrors(['title']);
    });

    it('validates event_type in real-time', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        Livewire::test(Edit::class, ['event' => $event])
            ->set('event_type', 'invalid')
            ->assertHasErrors(['event_type'])
            ->set('event_type', 'apertura')
            ->assertHasNoErrors(['event_type']);
    });
});

describe('Admin Events Edit - Date Handling', function () {
    it('auto-adjusts end_date when it is before start_date', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(4)->format('Y-m-d\TH:i'); // Before start

        $component = Livewire::test(Edit::class, ['event' => $event])
            ->set('start_date', $startDate)
            ->set('end_date', $endDate);

        // Trigger the update by setting start_date again
        $component->set('start_date', $startDate);

        // The component should auto-adjust end_date
        $adjustedEnd = Carbon::parse($startDate)->addHour()->format('Y-m-d\TH:i');
        expect($component->get('end_date'))->toBe($adjustedEnd);
    });

    it('handles all day events correctly', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program->id]);
        $event = ErasmusEvent::factory()->create([
            'program_id' => $program->id,
            'call_id' => $call->id,
        ]);

        $startDate = now()->addDays(5)->format('Y-m-d\TH:i');
        $endDate = now()->addDays(5)->addHours(2)->format('Y-m-d\TH:i');

        Livewire::test(Edit::class, ['event' => $event])
            ->set('start_date', $startDate)
            ->set('end_date', $endDate)
            ->set('is_all_day', true)
            ->call('update')
            ->assertDispatched('event-updated');

        $event->refresh();
        expect($event->isAllDay())->toBeTrue();
    });
});

describe('Admin Events Edit - Image Management', function () {
    it('can soft delete an existing image', function () {
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
        $media = $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        Livewire::test(Edit::class, ['event' => $event])
            ->call('confirmDeleteImage', $media->id)
            ->assertSet('imageToDelete', $media->id)
            ->assertSet('showDeleteImageModal', true)
            ->call('deleteImage')
            ->assertDispatched('image-deleted');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(0);
        expect($event->hasSoftDeletedImages())->toBeTrue();
    });

    it('can restore a soft-deleted image', function () {
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
        $media = $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        // Soft delete the image
        $event->softDeleteMediaById($media->id);

        Livewire::test(Edit::class, ['event' => $event])
            ->call('restoreImage', $media->id)
            ->assertDispatched('image-restored');

        $event->refresh();
        expect($event->getMedia('images')->count())->toBe(1);
        expect($event->hasSoftDeletedImages())->toBeFalse();
    });

    it('can force delete an image (permanent deletion)', function () {
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
        $media = $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        // Soft delete first
        $event->softDeleteMediaById($media->id);

        Livewire::test(Edit::class, ['event' => $event])
            ->call('confirmForceDeleteImage', $media->id)
            ->assertSet('imageToForceDelete', $media->id)
            ->assertSet('showForceDeleteImageModal', true)
            ->call('forceDeleteImage')
            ->assertDispatched('image-force-deleted');

        $event->refresh();
        expect($event->getMediaWithDeleted('images')->count())->toBe(0);
    });

    it('displays existing images correctly', function () {
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

        $component = Livewire::test(Edit::class, ['event' => $event]);

        $existingImages = $component->get('existingImages');
        expect($existingImages->count())->toBe(2);
    });

    it('displays soft-deleted images correctly', function () {
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
        $media = $event->addMedia($image->getRealPath())
            ->usingName($event->title)
            ->toMediaCollection('images');

        // Soft delete the image
        $event->softDeleteMediaById($media->id);

        $component = Livewire::test(Edit::class, ['event' => $event]);

        $deletedImages = $component->get('deletedImages');
        expect($deletedImages->count())->toBe(1);
    });
});

describe('Admin Events Edit - Program and Call Association', function () {
    it('resets call_id when program_id changes and call does not belong to new program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program1->id]);
        $call2 = Call::factory()->create(['program_id' => $program2->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
            'call_id' => $call1->id,
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event])
            ->assertSet('call_id', $call1->id)
            ->set('program_id', $program2->id)
            ->assertSet('call_id', null);
    });

    it('keeps call_id when program_id changes and call belongs to new program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $call = Call::factory()->create(['program_id' => $program2->id]);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
            'call_id' => null,
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event])
            ->set('program_id', $program2->id)
            ->set('call_id', $call->id)
            ->set('program_id', $program2->id) // Same program
            ->assertSet('call_id', $call->id);
    });

    it('filters available calls by selected program', function () {
        $user = User::factory()->create();
        $user->assignRole(Roles::ADMIN);
        $this->actingAs($user);

        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $call1 = Call::factory()->create(['program_id' => $program1->id, 'title' => 'Call 1']);
        $call2 = Call::factory()->create(['program_id' => $program2->id, 'title' => 'Call 2']);

        $event = ErasmusEvent::factory()->create([
            'program_id' => $program1->id,
        ]);

        $component = Livewire::test(Edit::class, ['event' => $event])
            ->set('program_id', $program1->id);

        $availableCalls = $component->get('availableCalls');
        expect($availableCalls->pluck('id')->toArray())->toContain($call1->id);
        expect($availableCalls->pluck('id')->toArray())->not->toContain($call2->id);
    });
});
