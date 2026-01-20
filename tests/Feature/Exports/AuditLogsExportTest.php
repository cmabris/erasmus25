<?php

use App\Exports\AuditLogsExport;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Spatie\Activitylog\Models\Activity;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| AuditLogsExport Tests
|--------------------------------------------------------------------------
|
| Tests para la exportación de logs de auditoría.
| Objetivo: Aumentar cobertura de 64.81% a 100%
|
*/

beforeEach(function () {
    $this->user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
});

describe('constructor and basic methods', function () {
    it('can be instantiated with empty filters', function () {
        $export = new AuditLogsExport([]);

        expect($export)->toBeInstanceOf(AuditLogsExport::class);
    });

    it('can be instantiated with filters', function () {
        $export = new AuditLogsExport([
            'search' => 'test',
            'filterModel' => 'App\Models\User',
        ]);

        expect($export)->toBeInstanceOf(AuditLogsExport::class);
    });

    it('returns correct headings', function () {
        $export = new AuditLogsExport([]);
        $headings = $export->headings();

        expect($headings)->toBeArray()
            ->and($headings)->toHaveCount(10)
            ->and($headings[0])->toBe(__('ID'))
            ->and($headings[1])->toBe(__('Fecha/Hora'))
            ->and($headings[2])->toBe(__('Usuario'))
            ->and($headings[3])->toBe(__('Email Usuario'))
            ->and($headings[9])->toBe(__('Cambios'));
    });

    it('returns correct sheet title', function () {
        $export = new AuditLogsExport([]);
        $title = $export->title();

        expect($title)->toBe(__('Logs de Auditoría'));
    });

    it('applies correct styles', function () {
        $export = new AuditLogsExport([]);

        $worksheet = $this->createMock(Worksheet::class);
        $styles = $export->styles($worksheet);

        expect($styles)->toBeArray()
            ->and($styles[1])->toBe(['font' => ['bold' => true]]);
    });
});

describe('collection method with filters', function () {
    beforeEach(function () {
        // Clear any pre-existing activities
        Activity::query()->delete();
    });

    it('returns all activities when no filters applied', function () {
        activity()
            ->causedBy($this->user)
            ->log('test_activity');

        $export = new AuditLogsExport([]);
        $collection = $export->query()->get();

        expect($collection)->toBeInstanceOf(Collection::class)
            ->and($collection)->toHaveCount(1);
    });

    it('applies search filter on description', function () {
        activity()
            ->causedBy($this->user)
            ->log('find_this_action');

        activity()
            ->causedBy($this->user)
            ->log('different_action');

        $export = new AuditLogsExport(['search' => 'find_this']);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->description)->toBe('find_this_action');
    });

    it('applies search filter on subject_type', function () {
        // Manually create activities without model observers
        Activity::create([
            'log_name' => 'default',
            'description' => 'test',
            'subject_type' => 'App\Models\Program',
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'test',
            'subject_type' => 'App\Models\AcademicYear',
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        $export = new AuditLogsExport(['search' => 'Program']);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->subject_type)->toContain('Program');
    });

    it('applies filterModel filter', function () {
        Activity::create([
            'log_name' => 'default',
            'description' => 'test',
            'subject_type' => Program::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'test',
            'subject_type' => AcademicYear::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        $export = new AuditLogsExport(['filterModel' => Program::class]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->subject_type)->toBe(Program::class);
    });

    it('applies filterCauserId filter', function () {
        $user2 = User::factory()->create();

        activity()
            ->causedBy($this->user)
            ->log('by_user1');

        activity()
            ->causedBy($user2)
            ->log('by_user2');

        $export = new AuditLogsExport(['filterCauserId' => $this->user->id]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->causer_id)->toBe($this->user->id);
    });

    it('applies filterDescription filter', function () {
        activity()
            ->causedBy($this->user)
            ->log('unique_description_123');

        activity()
            ->causedBy($this->user)
            ->log('another_description');

        $export = new AuditLogsExport(['filterDescription' => 'unique_description_123']);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->description)->toBe('unique_description_123');
    });

    it('applies filterLogName filter', function () {
        activity('audit_log')
            ->causedBy($this->user)
            ->log('audit_entry');

        activity('system_log')
            ->causedBy($this->user)
            ->log('system_entry');

        $export = new AuditLogsExport(['filterLogName' => 'audit_log']);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->log_name)->toBe('audit_log');
    });

    it('applies filterDateFrom filter', function () {
        // Create old activity
        $oldActivity = activity()
            ->causedBy($this->user)
            ->log('old_entry');
        Activity::where('id', $oldActivity->id)->update(['created_at' => now()->subDays(10)]);

        // Create new activity
        activity()
            ->causedBy($this->user)
            ->log('new_entry');

        $export = new AuditLogsExport(['filterDateFrom' => now()->subDays(2)->toDateString()]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->description)->toBe('new_entry');
    });

    it('applies filterDateTo filter', function () {
        // Create old activity
        $oldActivity = activity()
            ->causedBy($this->user)
            ->log('old_entry');
        Activity::where('id', $oldActivity->id)->update(['created_at' => now()->subDays(10)]);

        // Create new activity
        activity()
            ->causedBy($this->user)
            ->log('new_entry');

        $export = new AuditLogsExport(['filterDateTo' => now()->subDays(5)->toDateString()]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->description)->toBe('old_entry');
    });

    it('applies sorting', function () {
        activity()
            ->causedBy($this->user)
            ->log('aaa_first');

        activity()
            ->causedBy($this->user)
            ->log('zzz_last');

        $export = new AuditLogsExport([
            'sortField' => 'description',
            'sortDirection' => 'asc',
        ]);
        $collection = $export->query()->get();

        expect($collection->first()->description)->toBe('aaa_first');
    });

    it('applies multiple filters together', function () {
        $user2 = User::factory()->create();

        Activity::create([
            'log_name' => 'default',
            'description' => 'target_entry',
            'subject_type' => Program::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'other_entry',
            'subject_type' => Program::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $user2->id,
            'properties' => [],
        ]);

        Activity::create([
            'log_name' => 'default',
            'description' => 'different_entry',
            'subject_type' => AcademicYear::class,
            'subject_id' => 1,
            'causer_type' => User::class,
            'causer_id' => $this->user->id,
            'properties' => [],
        ]);

        $export = new AuditLogsExport([
            'filterModel' => Program::class,
            'filterCauserId' => $this->user->id,
        ]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->causer_id)->toBe($this->user->id)
            ->and($collection->first()->subject_type)->toBe(Program::class);
    });
});

describe('map method', function () {
    it('maps activity with causer correctly', function () {
        $program = Program::factory()->create(['name' => 'Test Program']);

        $activity = activity()
            ->performedOn($program)
            ->causedBy($this->user)
            ->log('created');

        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped)->toBeArray()
            ->and($mapped[0])->toBe($activity->id)
            ->and($mapped[2])->toBe('Test User')
            ->and($mapped[3])->toBe('test@example.com')
            ->and($mapped[4])->toBe(__('Creado'));
    });

    it('maps activity without causer correctly', function () {
        $program = Program::factory()->create();

        $activity = activity()
            ->performedOn($program)
            ->log('created');

        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[2])->toBe(__('common.messages.system'))
            ->and($mapped[3])->toBe('-');
    });
});

describe('getModelDisplayName method', function () {
    it('returns dash for null subject type', function () {
        $activity = activity()->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe('-');
    });

    it('returns correct display name for Program', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Programa'));
    });

    it('returns correct display name for Call', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $activity = activity()->performedOn($call)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Convocatoria'));
    });

    it('returns correct display name for NewsPost', function () {
        $newsPost = NewsPost::factory()->create();
        $activity = activity()->performedOn($newsPost)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Noticia'));
    });

    it('returns correct display name for Document', function () {
        $category = DocumentCategory::factory()->create();
        $document = Document::factory()->create(['category_id' => $category->id]);
        $activity = activity()->performedOn($document)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Documento'));
    });

    it('returns correct display name for ErasmusEvent', function () {
        $event = ErasmusEvent::factory()->create();
        $activity = activity()->performedOn($event)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Evento'));
    });

    it('returns correct display name for AcademicYear', function () {
        $academicYear = AcademicYear::factory()->create();
        $activity = activity()->performedOn($academicYear)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Año Académico'));
    });

    it('returns correct display name for DocumentCategory', function () {
        $category = DocumentCategory::factory()->create();
        $activity = activity()->performedOn($category)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Categoría de Documento'));
    });

    it('returns correct display name for NewsTag', function () {
        $tag = NewsTag::factory()->create();
        $activity = activity()->performedOn($tag)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Etiqueta de Noticia'));
    });

    it('returns correct display name for Resolution', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $resolution = Resolution::factory()->create(['call_id' => $call->id]);
        $activity = activity()->performedOn($resolution)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Resolución'));
    });

    it('returns class basename for unknown model', function () {
        $user = User::factory()->create();
        $activity = activity()->performedOn($user)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe('User');
    });
});

describe('getDescriptionDisplayName method', function () {
    it('returns Creado for created', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('created');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Creado'));
    });

    it('returns Actualizado for updated', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('updated');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Actualizado'));
    });

    it('returns Eliminado for deleted', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('deleted');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Eliminado'));
    });

    it('returns Publicado for publish', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('publish');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Publicado'));
    });

    it('returns Publicado for published', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('published');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Publicado'));
    });

    it('returns Archivado for archive', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('archive');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Archivado'));
    });

    it('returns Archivado for archived', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('archived');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Archivado'));
    });

    it('returns Restaurado for restore', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('restore');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Restaurado'));
    });

    it('returns Restaurado for restored', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('restored');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe(__('Restaurado'));
    });

    it('returns capitalized description for unknown action', function () {
        $program = Program::factory()->create();
        $activity = activity()->performedOn($program)->log('custom_action');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[4])->toBe('Custom_action');
    });
});

describe('getSubjectTitle method', function () {
    it('returns dash for null subject', function () {
        $activity = activity()->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[7])->toBe('-');
    });

    it('returns title when subject has title property', function () {
        $newsPost = NewsPost::factory()->create(['title' => 'My News Title']);
        $activity = activity()->performedOn($newsPost)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[7])->toBe('My News Title');
    });

    it('returns name when subject has name property but not title', function () {
        $program = Program::factory()->create(['name' => 'My Program Name']);
        $activity = activity()->performedOn($program)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[7])->toBe('My Program Name');
    });

    it('returns Registro #id for subject without title or name', function () {
        $academicYear = AcademicYear::factory()->create();
        $activity = activity()->performedOn($academicYear)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        // AcademicYear has 'year' but not 'title' or 'name'
        expect($mapped[7])->toContain('Registro #');
    });
});

describe('formatChangesSummary method', function () {
    it('returns dash when properties is null', function () {
        $program = Program::factory()->create();

        // Create activity and then manually update properties to null
        $activity = activity()
            ->performedOn($program)
            ->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        // Force null properties
        $activity->properties = null;

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        // When properties is null, should return '-'
        expect($mapped[9])->toBe('-');
    });

    it('returns Sin cambios when properties is empty', function () {
        $activity = activity()->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        // When activity has empty properties, it returns 'Sin cambios' (no changes)
        expect($mapped[9])->toBe(__('Sin cambios'));
    });

    it('returns Sin cambios when properties have no old/attributes or no changes', function () {
        $program = Program::factory()->create();
        $activity = activity()
            ->performedOn($program)
            ->withProperties(['key' => 'value'])
            ->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[9])->toBe(__('Sin cambios'));
    });

    it('lists changed fields when old and attributes differ', function () {
        $program = Program::factory()->create();
        $activity = activity()
            ->performedOn($program)
            ->withProperties([
                'old' => ['name' => 'Old Name', 'description' => 'Old Desc'],
                'attributes' => ['name' => 'New Name', 'description' => 'Old Desc'],
            ])
            ->log('updated');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[9])->toContain('name');
    });

    it('truncates when more than 10 changes', function () {
        $program = Program::factory()->create();

        $old = [];
        $new = [];
        for ($i = 1; $i <= 15; $i++) {
            $old["field_{$i}"] = "old_{$i}";
            $new["field_{$i}"] = "new_{$i}";
        }

        $activity = activity()
            ->performedOn($program)
            ->withProperties([
                'old' => $old,
                'attributes' => $new,
            ])
            ->log('updated');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[9])->toContain('...');
    });

    it('handles collection properties', function () {
        $program = Program::factory()->create();
        $activity = activity()
            ->performedOn($program)
            ->withProperties(collect([
                'old' => ['name' => 'Old'],
                'attributes' => ['name' => 'New'],
            ]))
            ->log('updated');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[9])->toContain('name');
    });
});

describe('CallPhase model display name', function () {
    it('returns correct display name for CallPhase', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = \App\Models\CallPhase::factory()->create(['call_id' => $call->id]);
        $activity = activity()->performedOn($phase)->log('test');
        $activity = Activity::with(['causer', 'subject'])->find($activity->id);

        $export = new AuditLogsExport([]);
        $mapped = $export->map($activity);

        expect($mapped[5])->toBe(__('Fase de Convocatoria'));
    });
});
