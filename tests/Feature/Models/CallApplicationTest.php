<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallApplication;
use App\Models\Program;

it('belongs to a call', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $application = CallApplication::factory()->create(['call_id' => $call->id]);

    expect($application->call)->toBeInstanceOf(Call::class)
        ->and($application->call->id)->toBe($call->id);
});

it('is deleted in cascade when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $application = CallApplication::factory()->create(['call_id' => $call->id]);

    $call->delete();

    expect(CallApplication::find($application->id))->toBeNull();
});

