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

it('sets created_by to null when creator user is deleted', function () {
    $user = User::factory()->create();
    $event = ErasmusEvent::factory()->create([
        'created_by' => $user->id,
    ]);

    $user->delete();
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
