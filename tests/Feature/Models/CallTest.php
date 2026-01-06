<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallApplication;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;

it('belongs to a program', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($call->program)->toBeInstanceOf(Program::class)
        ->and($call->program->id)->toBe($program->id);
});

it('belongs to an academic year', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    expect($call->academicYear)->toBeInstanceOf(AcademicYear::class)
        ->and($call->academicYear->id)->toBe($academicYear->id);
});

it('belongs to a creator user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    expect($call->creator)->toBeInstanceOf(User::class)
        ->and($call->creator->id)->toBe($user->id);
});

it('can have null creator', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => null,
    ]);

    expect($call->creator)->toBeNull();
});

it('belongs to an updater user', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'updated_by' => $user->id,
    ]);

    expect($call->updater)->toBeInstanceOf(User::class)
        ->and($call->updater->id)->toBe($user->id);
});

it('can have null updater', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'updated_by' => null,
    ]);

    expect($call->updater)->toBeNull();
});

it('has many phases', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    CallPhase::factory()->count(3)->create(['call_id' => $call->id]);

    expect($call->phases)->toHaveCount(3)
        ->and($call->phases->first())->toBeInstanceOf(CallPhase::class);
});

it('orders phases by order field', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase1 = CallPhase::factory()->create(['call_id' => $call->id, 'order' => 3]);
    $phase2 = CallPhase::factory()->create(['call_id' => $call->id, 'order' => 1]);
    $phase3 = CallPhase::factory()->create(['call_id' => $call->id, 'order' => 2]);

    $phases = $call->phases;

    expect($phases[0]->id)->toBe($phase2->id)
        ->and($phases[1]->id)->toBe($phase3->id)
        ->and($phases[2]->id)->toBe($phase1->id);
});

it('has many applications', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    CallApplication::factory()->count(5)->create(['call_id' => $call->id]);

    expect($call->applications)->toHaveCount(5)
        ->and($call->applications->first())->toBeInstanceOf(CallApplication::class);
});

it('has many resolutions', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase = CallPhase::factory()->create(['call_id' => $call->id]);
    Resolution::factory()->count(2)->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
    ]);

    expect($call->resolutions)->toHaveCount(2)
        ->and($call->resolutions->first())->toBeInstanceOf(Resolution::class);
});

it('deletes phases in cascade when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $phase1 = CallPhase::factory()->create(['call_id' => $call->id]);
    $phase2 = CallPhase::factory()->create(['call_id' => $call->id]);

    $call->delete();

    expect(CallPhase::find($phase1->id))->toBeNull()
        ->and(CallPhase::find($phase2->id))->toBeNull();
});

it('deletes applications in cascade when call is deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);
    $application1 = CallApplication::factory()->create(['call_id' => $call->id]);
    $application2 = CallApplication::factory()->create(['call_id' => $call->id]);

    $call->delete();

    expect(CallApplication::find($application1->id))->toBeNull()
        ->and(CallApplication::find($application2->id))->toBeNull();
});

it('deletes resolutions in cascade when call is deleted', function () {
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

    $call->delete();

    expect(Resolution::find($resolution1->id))->toBeNull()
        ->and(Resolution::find($resolution2->id))->toBeNull();
});

it('sets created_by to null when creator user is force deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $call->refresh();

    expect($call->created_by)->toBeNull()
        ->and($call->creator)->toBeNull();
});

it('sets updated_by to null when updater user is force deleted', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $user = User::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'updated_by' => $user->id,
    ]);

    // Force delete to trigger foreign key constraint
    $user->forceDelete();
    $call->refresh();

    expect($call->updated_by)->toBeNull()
        ->and($call->updater)->toBeNull();
});

it('generates slug automatically when slug is empty', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'title' => 'Test Call Title',
        'slug' => '', // Empty slug
        'type' => 'alumnado',
        'modality' => 'corta',
        'number_of_places' => 10,
        'destinations' => ['Spain'],
        'status' => 'borrador',
    ]);

    expect($call->slug)->toBe('test-call-title');
});
