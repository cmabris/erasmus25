<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;

it('belongs to a call', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);

    expect($phase->call)->toBeInstanceOf(Call::class)
        ->and($phase->call->id)->toBe($call->id);
});

it('has many resolutions', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    Resolution::factory()->count(3)->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    expect($phase->resolutions)->toHaveCount(3)
        ->and($phase->resolutions->first())->toBeInstanceOf(Resolution::class);
});

it('deletes resolutions in cascade when phase is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    $resolution1 = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);
    $resolution2 = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    $phase->delete();

    expect(Resolution::find($resolution1->id))->toBeNull()
        ->and(Resolution::find($resolution2->id))->toBeNull();
});

it('is deleted in cascade when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);

    $call->delete();

    expect(CallPhase::find($phase->id))->toBeNull();
});

