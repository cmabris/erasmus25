<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;

it('belongs to a call', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    expect($resolution->call)->toBeInstanceOf(Call::class)
        ->and($resolution->call->id)->toBe($call->id);
});

it('belongs to a call phase', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    expect($resolution->callPhase)->toBeInstanceOf(CallPhase::class)
        ->and($resolution->callPhase->id)->toBe($phase->id);
});

it('belongs to a creator user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $user = User::factory()->create();
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'created_by' => $user->id,
    ]);

    expect($resolution->creator)->toBeInstanceOf(User::class)
        ->and($resolution->creator->id)->toBe($user->id);
});

it('can have null creator', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'created_by' => null,
    ]);

    expect($resolution->creator)->toBeNull();
});

it('is deleted in cascade when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    $call->delete();

    expect(Resolution::find($resolution->id))->toBeNull();
});

it('is deleted in cascade when call phase is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    $phase->delete();

    expect(Resolution::find($resolution->id))->toBeNull();
});

it('sets created_by to null when creator user is force deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $user = User::factory()->create();
    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'created_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $resolution->refresh();

    expect($resolution->created_by)->toBeNull()
        ->and($resolution->creator)->toBeNull();
});
