<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\Program;
use App\Models\User;

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
