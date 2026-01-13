<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('Automatic Logging - Program Model', function () {
    it('creates activity log when program is created', function () {
        $program = Program::factory()->create();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->causer_id)->toBe($this->user->id)
            ->and($activity->causer_type)->toBe(User::class)
            ->and($activity->subject_type)->toBe(Program::class)
            ->and($activity->subject_id)->toBe($program->id);
    });

    it('creates activity log when program is updated', function () {
        $program = Program::factory()->create(['name' => 'Original Name']);

        // Limpiar el log de creación
        Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->delete();

        $program->update(['name' => 'Updated Name']);

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'updated')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('updated')
            ->and($activity->properties)->toHaveKey('attributes')
            ->and($activity->properties)->toHaveKey('old');
    });

    it('creates activity log when program is deleted', function () {
        $program = Program::factory()->create();

        // Limpiar el log de creación
        Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->delete();

        $program->delete();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'deleted')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('deleted');
    });

    it('logs only configured fields', function () {
        $program = Program::factory()->create();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'created')
            ->first();

        $properties = $activity->properties;
        $attributes = $properties['attributes'] ?? [];

        // Verificar que se loguean los campos configurados (slug está en dontLogIfAttributesChangedOnly)
        expect($attributes)->toHaveKey('name')
            ->and($attributes)->toHaveKey('code');
    });

    it('does not log updated_at field', function () {
        $program = Program::factory()->create(['name' => 'Original']);

        // Limpiar el log de creación
        Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->delete();

        $program->update(['name' => 'Updated']);

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'updated')
            ->first();

        $properties = $activity->properties;
        $attributes = $properties['attributes'] ?? [];

        // Verificar que updated_at no está en los atributos logueados
        expect($attributes)->not->toHaveKey('updated_at');
    });
});

describe('Automatic Logging - Call Model', function () {
    it('creates activity log when call is created', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Call::class)
            ->and($activity->subject_id)->toBe($call->id);
    });

    it('creates activity log when call is updated', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Limpiar el log de creación
        Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->delete();

        $call->update(['title' => 'Updated Title']);

        $activity = Activity::where('subject_type', Call::class)
            ->where('subject_id', $call->id)
            ->where('description', 'updated')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('updated');
    });
});

describe('Automatic Logging - NewsPost Model', function () {
    it('creates activity log when news post is created', function () {
        $newsPost = NewsPost::factory()->create();

        $activity = Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(NewsPost::class)
            ->and($activity->subject_id)->toBe($newsPost->id);
    });

    it('creates activity log when news post is updated', function () {
        $newsPost = NewsPost::factory()->create();

        // Limpiar el log de creación
        Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->delete();

        $newsPost->update(['title' => 'Updated Title']);

        $activity = Activity::where('subject_type', NewsPost::class)
            ->where('subject_id', $newsPost->id)
            ->where('description', 'updated')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->description)->toBe('updated');
    });
});

describe('Automatic Logging - Document Model', function () {
    it('creates activity log when document is created', function () {
        $document = Document::factory()->create();

        $activity = Activity::where('subject_type', Document::class)
            ->where('subject_id', $document->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(Document::class)
            ->and($activity->subject_id)->toBe($document->id);
    });
});

describe('Automatic Logging - ErasmusEvent Model', function () {
    it('creates activity log when event is created', function () {
        $program = Program::factory()->create();
        $event = ErasmusEvent::factory()->create(['program_id' => $program->id]);

        $activity = Activity::where('subject_type', ErasmusEvent::class)
            ->where('subject_id', $event->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(ErasmusEvent::class)
            ->and($activity->subject_id)->toBe($event->id);
    });
});

describe('Automatic Logging - AcademicYear Model', function () {
    it('creates activity log when academic year is created', function () {
        $academicYear = AcademicYear::factory()->create();

        $activity = Activity::where('subject_type', AcademicYear::class)
            ->where('subject_id', $academicYear->id)
            ->where('description', 'created')
            ->first();

        expect($activity)->not->toBeNull()
            ->and($activity->subject_type)->toBe(AcademicYear::class)
            ->and($activity->subject_id)->toBe($academicYear->id);
    });
});

describe('Automatic Logging - Relationships', function () {
    it('correctly sets causer relationship', function () {
        $program = Program::factory()->create();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'created')
            ->first();

        expect($activity->causer)->toBeInstanceOf(User::class)
            ->and($activity->causer->id)->toBe($this->user->id);
    });

    it('correctly sets subject relationship', function () {
        $program = Program::factory()->create();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'created')
            ->first();

        expect($activity->subject)->toBeInstanceOf(Program::class)
            ->and($activity->subject->id)->toBe($program->id);
    });

    it('handles null causer when created by system', function () {
        // Simular creación sin usuario autenticado
        auth()->logout();

        $program = Program::factory()->create();

        $activity = Activity::where('subject_type', Program::class)
            ->where('subject_id', $program->id)
            ->where('description', 'created')
            ->first();

        // El causer puede ser null si no hay usuario autenticado
        expect($activity)->not->toBeNull();
    });
});
