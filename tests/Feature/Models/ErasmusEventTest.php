<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

it('belongs to a program', function () {
    $program = Program::factory()->create(['code' => 'KA999', 'name' => 'Programa Test', 'slug' => 'programa-test']);
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => $user->id,
    ]);

    expect($event->program)->toBeInstanceOf(Program::class)
        ->and($event->program->id)->toBe($program->id);
});

it('can have null program', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'program_id' => null,
        'created_by' => $user->id,
    ]);

    expect($event->program)->toBeNull();
});

it('belongs to a call', function () {
    $program = Program::factory()->create(['code' => 'KA994', 'name' => 'Programa Test C', 'slug' => 'programa-test-c']);
    $academicYear = AcademicYear::factory()->create(['year' => '2026-2027']);
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'call_id' => $call->id,
        'created_by' => $user->id,
    ]);

    expect($event->call)->toBeInstanceOf(Call::class)
        ->and($event->call->id)->toBe($call->id);
});

it('can have null call', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'call_id' => null,
        'created_by' => $user->id,
    ]);

    expect($event->call)->toBeNull();
});

it('belongs to a creator user', function () {
    $program = Program::factory()->create(['code' => 'KA1xx', 'name' => 'Educación Escolar', 'slug' => 'educacion-escolar']);
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => $user->id,
    ]);

    expect($event->creator)->toBeInstanceOf(User::class)
        ->and($event->creator->id)->toBe($user->id);
});

it('can have null creator', function () {
    $program = Program::factory()->create(['code' => 'KA998', 'name' => 'Programa Test 2', 'slug' => 'programa-test-2']);
    $event = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => null,
    ]);

    expect($event->creator)->toBeNull();
});

it('does not set program_id to null when program is soft deleted', function () {
    $program = Program::factory()->create(['code' => 'KA999-TEST', 'slug' => 'ka999-test']);
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => $user->id,
    ]);

    $program->delete(); // Soft delete
    $event->refresh();

    // With SoftDeletes, program_id is not set to null because the program still exists
    // However, Eloquent excludes soft-deleted records from relationships, so $event->program will be null
    expect($event->program_id)->toBe($program->id)
        ->and($event->program)->toBeNull() // Eloquent excludes soft-deleted records
        ->and($program->fresh()->trashed())->toBeTrue();
});

it('sets call_id to null when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'call_id' => $call->id,
        'created_by' => $user->id,
    ]);

    $call->delete();
    $event->refresh();

    expect($event->call_id)->toBeNull()
        ->and($event->call)->toBeNull();
});

it('sets created_by to null when creator user is force deleted', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $event->refresh();

    expect($event->created_by)->toBeNull()
        ->and($event->creator)->toBeNull();
});

// ============================================
// SOFT DELETES TESTS
// ============================================

it('can soft delete an event', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $event->delete();

    expect($event->trashed())->toBeTrue()
        ->and($event->deleted_at)->not->toBeNull()
        ->and(ErasmusEvent::find($event->id))->toBeNull()
        ->and(ErasmusEvent::withTrashed()->find($event->id))->not->toBeNull();
});

it('excludes soft deleted events from queries by default', function () {
    $user = User::factory()->create();
    $event1 = ErasmusEvent::factory()->create(['created_by' => $user->id]);
    $event2 = ErasmusEvent::factory()->create(['created_by' => $user->id]);

    $event1->delete();

    $events = ErasmusEvent::all();

    expect($events)->toHaveCount(1)
        ->and($events->first()->id)->toBe($event2->id);
});

it('can restore a soft deleted event', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $event->delete();
    expect($event->trashed())->toBeTrue();

    $event->restore();

    expect($event->trashed())->toBeFalse()
        ->and($event->deleted_at)->toBeNull()
        ->and(ErasmusEvent::find($event->id))->not->toBeNull();
});

it('can force delete an event', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);
    $eventId = $event->id;

    $event->forceDelete();

    expect(ErasmusEvent::withTrashed()->find($eventId))->toBeNull()
        ->and(ErasmusEvent::onlyTrashed()->find($eventId))->toBeNull();
});

it('scopes work correctly with soft deletes', function () {
    $user = User::factory()->create();
    $program = Program::factory()->create();

    $event1 = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => $user->id,
        'is_public' => true,
        'start_date' => now()->addDay(),
    ]);

    $event2 = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'created_by' => $user->id,
        'is_public' => true,
        'start_date' => now()->addDays(2),
    ]);

    $event1->delete();

    // Public scope should exclude soft deleted events
    $publicEvents = ErasmusEvent::public()->get();
    expect($publicEvents)->toHaveCount(1)
        ->and($publicEvents->first()->id)->toBe($event2->id);

    // Upcoming scope should exclude soft deleted events
    $upcomingEvents = ErasmusEvent::upcoming()->get();
    expect($upcomingEvents)->toHaveCount(1)
        ->and($upcomingEvents->first()->id)->toBe($event2->id);

    // ForProgram scope should exclude soft deleted events
    $programEvents = ErasmusEvent::forProgram($program->id)->get();
    expect($programEvents)->toHaveCount(1)
        ->and($programEvents->first()->id)->toBe($event2->id);
});

// ============================================
// MEDIA LIBRARY TESTS
// ============================================

it('can add images to an event', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName($image->getClientOriginalName())
        ->toMediaCollection('images');

    expect($event->hasMedia('images'))->toBeTrue()
        ->and($event->getMedia('images'))->toHaveCount(1)
        ->and($media->collection_name)->toBe('images')
        ->and($media->model_type)->toBe(ErasmusEvent::class)
        ->and($media->model_id)->toBe($event->id);
});

it('can add multiple images to an event', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName($image1->getClientOriginalName())
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName($image2->getClientOriginalName())
        ->toMediaCollection('images');

    expect($event->hasMedia('images'))->toBeTrue()
        ->and($event->getMedia('images'))->toHaveCount(2);
});

it('can get first media from images collection', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName($image1->getClientOriginalName())
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName($image2->getClientOriginalName())
        ->toMediaCollection('images');

    $firstMedia = $event->getFirstMedia('images');

    expect($firstMedia)->not->toBeNull()
        ->and($firstMedia->id)->toBe($media1->id);
});

it('can delete media from event', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName($image->getClientOriginalName())
        ->toMediaCollection('images');

    expect($event->hasMedia('images'))->toBeTrue();

    $media->delete();

    expect($event->fresh()->hasMedia('images'))->toBeFalse();
});

// ============================================
// SCOPES TESTS - Additional Coverage
// ============================================

it('can scope to past events', function () {
    $user = User::factory()->create();

    // Past event
    $pastEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->subDays(5),
    ]);

    // Future event
    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDays(5),
    ]);

    // Today's event (should not be included in past)
    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now(),
    ]);

    $pastEvents = ErasmusEvent::past()->get();

    expect($pastEvents)->toHaveCount(1)
        ->and($pastEvents->first()->id)->toBe($pastEvent->id);
});

it('can scope to events for a specific date using Carbon', function () {
    $user = User::factory()->create();
    $targetDate = now()->addDays(3);

    $eventOnDate = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => $targetDate,
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDays(5),
    ]);

    $eventsForDate = ErasmusEvent::forDate($targetDate)->get();

    expect($eventsForDate)->toHaveCount(1)
        ->and($eventsForDate->first()->id)->toBe($eventOnDate->id);
});

it('can scope to events for a specific date using string', function () {
    $user = User::factory()->create();
    $targetDate = '2025-06-15';

    $eventOnDate = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => $targetDate,
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-16',
    ]);

    $eventsForDate = ErasmusEvent::forDate($targetDate)->get();

    expect($eventsForDate)->toHaveCount(1)
        ->and($eventsForDate->first()->id)->toBe($eventOnDate->id);
});

it('can scope to events for a specific month', function () {
    $user = User::factory()->create();

    $eventInMonth = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-15',
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-07-15',
    ]);

    $eventsForMonth = ErasmusEvent::forMonth(2025, 6)->get();

    expect($eventsForMonth)->toHaveCount(1)
        ->and($eventsForMonth->first()->id)->toBe($eventInMonth->id);
});

it('can scope to events for a specific call', function () {
    $user = User::factory()->create();
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $eventForCall = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'call_id' => $call->id,
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'call_id' => null,
    ]);

    $eventsForCall = ErasmusEvent::forCall($call->id)->get();

    expect($eventsForCall)->toHaveCount(1)
        ->and($eventsForCall->first()->id)->toBe($eventForCall->id);
});

it('can scope to events by type', function () {
    $user = User::factory()->create();

    $aperturaEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'event_type' => 'apertura',
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'event_type' => 'cierre',
    ]);

    $aperturaEvents = ErasmusEvent::byType('apertura')->get();

    expect($aperturaEvents)->toHaveCount(1)
        ->and($aperturaEvents->first()->id)->toBe($aperturaEvent->id);
});

it('can scope to events in date range with Carbon objects', function () {
    $user = User::factory()->create();

    $eventInRange = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDays(5),
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDays(20),
    ]);

    $eventsInRange = ErasmusEvent::inDateRange(now(), now()->addDays(10))->get();

    expect($eventsInRange)->toHaveCount(1)
        ->and($eventsInRange->first()->id)->toBe($eventInRange->id);
});

it('can scope to events in date range with string dates', function () {
    $user = User::factory()->create();

    $eventInRange = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-15',
    ]);

    ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-07-15',
    ]);

    $eventsInRange = ErasmusEvent::inDateRange('2025-06-01', '2025-06-30')->get();

    expect($eventsInRange)->toHaveCount(1)
        ->and($eventsInRange->first()->id)->toBe($eventInRange->id);
});

// ============================================
// HELPER METHODS TESTS - Additional Coverage
// ============================================

it('correctly identifies an upcoming event', function () {
    $user = User::factory()->create();

    $futureEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDays(5),
    ]);

    expect($futureEvent->isUpcoming())->toBeTrue();
});

it('correctly identifies an event is not upcoming if in past', function () {
    $user = User::factory()->create();

    $pastEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->subDays(5),
    ]);

    expect($pastEvent->isUpcoming())->toBeFalse();
});

it('correctly identifies an event is today', function () {
    $user = User::factory()->create();

    $todayEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now(),
    ]);

    expect($todayEvent->isToday())->toBeTrue();
});

it('correctly identifies an event is not today', function () {
    $user = User::factory()->create();

    $tomorrowEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->addDay(),
    ]);

    expect($tomorrowEvent->isToday())->toBeFalse();
});

it('correctly identifies a past event', function () {
    $user = User::factory()->create();

    $pastEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now()->subDays(5),
    ]);

    expect($pastEvent->isPast())->toBeTrue();
});

it('today event is not considered past', function () {
    $user = User::factory()->create();

    $todayEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now(),
    ]);

    expect($todayEvent->isPast())->toBeFalse();
});

it('calculates duration in hours', function () {
    $user = User::factory()->create();

    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now(),
        'end_date' => now()->addHours(3),
    ]);

    expect($event->duration())->toBe(3.0);
});

it('returns null duration when no end date', function () {
    $user = User::factory()->create();

    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => now(),
        'end_date' => null,
    ]);

    expect($event->duration())->toBeNull();
});

it('checks is_all_day field when set', function () {
    $user = User::factory()->create();

    $allDayEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'is_all_day' => true,
    ]);

    expect($allDayEvent->isAllDay())->toBeTrue();

    $notAllDayEvent = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'is_all_day' => false,
    ]);

    expect($notAllDayEvent->isAllDay())->toBeFalse();
});

// ============================================
// FORMATTED DATE RANGE ACCESSOR TESTS
// ============================================

it('formats date range for same day event with start and end time', function () {
    $user = User::factory()->create();

    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-15 10:00:00',
        'end_date' => '2025-06-15 14:00:00',
    ]);

    $formatted = $event->formatted_date_range;

    expect($formatted)->toContain('15')
        ->and($formatted)->toContain('10:00')
        ->and($formatted)->toContain('14:00');
});

it('formats date range for multi-day event', function () {
    $user = User::factory()->create();

    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-15 10:00:00',
        'end_date' => '2025-06-17 18:00:00',
    ]);

    $formatted = $event->formatted_date_range;

    expect($formatted)->toContain('Del')
        ->and($formatted)->toContain('al')
        ->and($formatted)->toContain('10:00')
        ->and($formatted)->toContain('18:00');
});

it('formats date range for event without end date', function () {
    $user = User::factory()->create();

    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
        'start_date' => '2025-06-15 10:00:00',
        'end_date' => null,
    ]);

    $formatted = $event->formatted_date_range;

    expect($formatted)->toContain('15')
        ->and($formatted)->toContain('10:00')
        ->and($formatted)->toContain('a las');
});

// ============================================
// MEDIA WITH FILTERS TESTS
// ============================================

it('can get media with callable filter', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName('event1.jpg')
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName('event2.jpg')
        ->toMediaCollection('images');

    // Filter by name using callable
    $filtered = $event->getMedia('images', fn ($m) => $m->name === 'Event Image 1');

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with array filter', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName('event1.jpg')
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName('event2.jpg')
        ->toMediaCollection('images');

    // Filter by name using array
    $filtered = $event->getMedia('images', ['name' => 'Event Image 1']);

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with deleted using callable filter', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName('event1.jpg')
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName('event2.jpg')
        ->toMediaCollection('images');

    // Soft delete media1
    $event->softDeleteMediaById($media1->id);

    // Get with deleted using callable filter
    $filtered = $event->getMediaWithDeleted('images', fn ($m) => $m->name === 'Event Image 1');

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

it('can get media with deleted using array filter', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName('event1.jpg')
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName('event2.jpg')
        ->toMediaCollection('images');

    // Get with deleted using array filter
    $filtered = $event->getMediaWithDeleted('images', ['name' => 'Event Image 1']);

    expect($filtered)->toHaveCount(1)
        ->and($filtered->first()->id)->toBe($media1->id);
});

// ============================================
// MEDIA SOFT DELETE ADDITIONAL TESTS
// ============================================

it('can restore a soft deleted media by id', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName('event.jpg')
        ->toMediaCollection('images');

    // Soft delete the media
    $event->softDeleteMediaById($media->id);
    expect($event->hasMedia('images'))->toBeFalse();

    // Restore the media
    $restored = $event->restoreMediaById($media->id);

    expect($restored)->toBeTrue()
        ->and($event->hasMedia('images'))->toBeTrue();
});

it('returns false when restoring non-existent media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $result = $event->restoreMediaById(99999);

    expect($result)->toBeFalse();
});

it('returns false when restoring non-deleted media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName('event.jpg')
        ->toMediaCollection('images');

    // Try to restore media that is not deleted
    $result = $event->restoreMediaById($media->id);

    expect($result)->toBeFalse();
});

it('can force delete media by id', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName('event.jpg')
        ->toMediaCollection('images');

    $mediaId = $media->id;

    // Force delete the media
    $result = $event->forceDeleteMediaById($mediaId);

    expect($result)->toBeTrue()
        ->and($event->hasMedia('images'))->toBeFalse()
        ->and($event->getMediaWithDeleted('images'))->toHaveCount(0);
});

it('returns false when force deleting non-existent media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $result = $event->forceDeleteMediaById(99999);

    expect($result)->toBeFalse();
});

it('can get soft deleted images', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $image1 = \Illuminate\Http\UploadedFile::fake()->image('event1.jpg', 800, 600);
    $image2 = \Illuminate\Http\UploadedFile::fake()->image('event2.jpg', 800, 600);

    $media1 = $event->addMedia($image1->getRealPath())
        ->usingName('Event Image 1')
        ->usingFileName('event1.jpg')
        ->toMediaCollection('images');

    $event->addMedia($image2->getRealPath())
        ->usingName('Event Image 2')
        ->usingFileName('event2.jpg')
        ->toMediaCollection('images');

    // Soft delete first image
    $event->softDeleteMediaById($media1->id);

    $softDeleted = $event->getSoftDeletedImages();

    expect($softDeleted)->toHaveCount(1)
        ->and($softDeleted->first()->id)->toBe($media1->id);
});

it('can check if has soft deleted images', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    expect($event->hasSoftDeletedImages())->toBeFalse();

    $image = \Illuminate\Http\UploadedFile::fake()->image('event.jpg', 800, 600);

    $media = $event->addMedia($image->getRealPath())
        ->usingName('Event Image')
        ->usingFileName('event.jpg')
        ->toMediaCollection('images');

    expect($event->hasSoftDeletedImages())->toBeFalse();

    // Soft delete the media
    $event->softDeleteMediaById($media->id);

    expect($event->hasSoftDeletedImages())->toBeTrue();
});

it('returns false when soft deleting non-existent media', function () {
    Storage::fake('public');

    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $result = $event->softDeleteMediaById(99999);

    expect($result)->toBeFalse();
});
