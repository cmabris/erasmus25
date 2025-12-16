<?php

use App\Models\AcademicYear;
use App\Models\AuditLog;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;

it('belongs to a user', function () {
    $user = User::factory()->create();
    $program = Program::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'user_id' => $user->id,
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);

    expect($auditLog->user)->toBeInstanceOf(User::class)
        ->and($auditLog->user->id)->toBe($user->id);
});

it('can have null user', function () {
    $program = Program::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'user_id' => null,
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);

    expect($auditLog->user)->toBeNull();
});

it('belongs to a polymorphic model', function () {
    $program = Program::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'user_id' => null,
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);

    expect($auditLog->model)->toBeInstanceOf(Program::class)
        ->and($auditLog->model->id)->toBe($program->id);
});

it('can reference different model types polymorphically', function () {
    $program = Program::factory()->create();
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $auditLog1 = AuditLog::factory()->create([
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);
    $auditLog2 = AuditLog::factory()->create([
        'model_type' => Call::class,
        'model_id' => $call->id,
    ]);

    expect($auditLog1->model)->toBeInstanceOf(Program::class)
        ->and($auditLog2->model)->toBeInstanceOf(Call::class);
});

it('sets user_id to null when user is deleted', function () {
    $user = User::factory()->create();
    $program = Program::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'user_id' => $user->id,
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);

    $user->delete();
    $auditLog->refresh();

    expect($auditLog->user_id)->toBeNull()
        ->and($auditLog->user)->toBeNull();
});

it('maintains model reference when model is deleted', function () {
    $program = Program::factory()->create();
    $auditLog = AuditLog::factory()->create([
        'model_type' => Program::class,
        'model_id' => $program->id,
    ]);

    $program->delete();
    $auditLog->refresh();

    // El audit log mantiene la referencia aunque el modelo haya sido eliminado
    expect($auditLog->model_type)->toBe(Program::class)
        ->and($auditLog->model_id)->toBe($program->id);
});

