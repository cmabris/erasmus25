<?php

use App\Exports\ResolutionsExport;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('ResolutionsExport - Basic Export', function () {
    it('exports all resolutions for a call when no filters applied', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        Resolution::factory()->count(5)->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $collection = $export->collection();

        expect($collection)->toHaveCount(5);
    });

    it('only exports resolutions for the specified call', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase1 = CallPhase::factory()->create(['call_id' => $call1->id]);
        $phase2 = CallPhase::factory()->create(['call_id' => $call2->id]);

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call1->id,
            'call_phase_id' => $phase1->id,
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call2->id,
            'call_phase_id' => $phase2->id,
        ]);

        $export = new ResolutionsExport(['call_id' => $call1->id]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('excludes soft deleted resolutions by default', function () {
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
        $resolution2->delete(); // Soft delete

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $collection = $export->collection();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->id)->toBe($resolution1->id);
    });

    it('has correct headings', function () {
        $export = new ResolutionsExport([]);
        $headings = $export->headings();

        expect($headings)->toBe([
            __('ID'),
            __('Título'),
            __('Convocatoria'),
            __('Fase'),
            __('Tipo'),
            __('Descripción'),
            __('Procedimiento de Evaluación'),
            __('Fecha Oficial'),
            __('Publicada'),
            __('Fecha Publicación'),
            __('Creador'),
            __('Fecha Creación'),
            __('Fecha Actualización'),
        ]);
    });

    it('has correct sheet title', function () {
        $export = new ResolutionsExport([]);
        $title = $export->title();

        expect($title)->toBe(__('Resoluciones'));
    });

    it('maps resolution data correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
        ]);
        $phase = CallPhase::factory()->create([
            'call_id' => $call->id,
            'name' => 'Fase Test',
        ]);
        $creator = User::factory()->create(['name' => 'Test User']);

        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'provisional',
            'title' => 'Resolución Test',
            'description' => 'Descripción corta',
            'evaluation_procedure' => 'Procedimiento corto',
            'official_date' => now()->setDate(2024, 6, 1),
            'published_at' => now()->setDate(2024, 6, 2)->setTime(10, 0),
            'created_by' => $creator->id,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[0])->toBe($resolution->id)
            ->and($mapped[1])->toBe('Resolución Test')
            ->and($mapped[2])->toBe('Convocatoria Test')
            ->and($mapped[3])->toBe('Fase Test')
            ->and($mapped[4])->toBe(__('common.resolutions.types.provisional'))
            ->and($mapped[5])->toBe('Descripción corta')
            ->and($mapped[6])->toBe('Procedimiento corto')
            ->and($mapped[7])->toBe('01/06/2024')
            ->and($mapped[8])->toBe(__('common.messages.yes'))
            ->and($mapped[9])->toBe('02/06/2024 10:00')
            ->and($mapped[10])->toBe('Test User');
    });

    it('handles null and empty values correctly', function () {
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
            'description' => null,
            'evaluation_procedure' => null,
            'published_at' => null,
            'created_by' => null,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[5])->toBe('-') // Null description
            ->and($mapped[6])->toBe('-') // Null evaluation_procedure
            ->and($mapped[8])->toBe(__('common.messages.no')) // Not published
            ->and($mapped[9])->toBe('-') // Null published_at
            ->and($mapped[10])->toBe(__('common.messages.system')); // Null creator
    });

    it('truncates long descriptions correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $longText = str_repeat('A', 150); // 150 characters
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'description' => $longText,
            'evaluation_procedure' => $longText,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[5])->toHaveLength(103) // 100 chars + '...'
            ->and($mapped[5])->toEndWith('...')
            ->and($mapped[6])->toHaveLength(103)
            ->and($mapped[6])->toEndWith('...');
    });

    it('does not truncate short descriptions', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $shortText = 'Short description'; // Less than 100 chars
        $resolution = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'description' => $shortText,
            'evaluation_procedure' => $shortText,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[5])->toBe($shortText)
            ->and($mapped[6])->toBe($shortText);
    });
});

describe('ResolutionsExport - Filters', function () {
    it('applies type filter', function () {
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
            'type' => 'provisional',
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'definitivo',
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'filterType' => 'provisional',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('applies published filter (published)', function () {
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
            'published_at' => now(),
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => null,
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'filterPublished' => '1',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('applies published filter (unpublished)', function () {
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
            'published_at' => now(),
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => null,
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'filterPublished' => '0',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->not->toContain($resolution1->id)
            ->and($ids)->toContain($resolution2->id);
    });

    it('applies phase filter', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase1 = CallPhase::factory()->create(['call_id' => $call->id, 'name' => 'Fase 1']);
        $phase2 = CallPhase::factory()->create(['call_id' => $call->id, 'name' => 'Fase 2']);

        $resolution1 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase1->id,
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase2->id,
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'filterPhase' => $phase1->id,
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('applies search filter by title', function () {
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
            'title' => 'Resolución Provisional',
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'Resolución Definitiva',
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'search' => 'Provisional',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('applies search filter by description', function () {
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
            'description' => 'Esta es una descripción sobre movilidad',
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'description' => 'Esta es otra descripción diferente',
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'search' => 'movilidad',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id);
    });

    it('includes only deleted resolutions when showDeleted is 1', function () {
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
        $resolution2->delete(); // Soft delete

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'showDeleted' => '1',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->not->toContain($resolution1->id)
            ->and($ids)->toContain($resolution2->id);
    });

    it('applies sorting', function () {
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
            'title' => 'B Resolución',
            'official_date' => now()->setDate(2024, 6, 1),
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'title' => 'A Resolución',
            'official_date' => now()->setDate(2024, 6, 2),
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'sortField' => 'official_date',
            'sortDirection' => 'desc',
        ]);
        $collection = $export->collection();

        $dates = $collection->pluck('official_date')->toArray();
        expect($dates[0]->format('Y-m-d'))->toBe('2024-06-02')
            ->and($dates[1]->format('Y-m-d'))->toBe('2024-06-01');
    });

    it('applies multiple filters together', function () {
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
            'type' => 'provisional',
            'published_at' => now(),
        ]);
        $resolution2 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'definitivo',
            'published_at' => now(),
        ]);
        $resolution3 = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'type' => 'provisional',
            'published_at' => null,
        ]);

        $export = new ResolutionsExport([
            'call_id' => $call->id,
            'filterType' => 'provisional',
            'filterPublished' => '1',
        ]);
        $collection = $export->collection();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($resolution1->id)
            ->and($ids)->not->toContain($resolution2->id)
            ->and($ids)->not->toContain($resolution3->id);
    });
});

describe('ResolutionsExport - Edge Cases for Labels', function () {
    it('handles null type correctly', function () {
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
            'type' => 'provisional', // Create with valid type first
        ]);

        // Force null type on the model attribute after creation
        $resolution->type = null;

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[4])->toBe('-');
    });

    it('handles unknown type with default value', function () {
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
            'type' => 'provisional', // Create with valid type first
        ]);

        // Force an unknown type directly on the model attribute
        $resolution->type = 'unknown_type';

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[4])->toBe('unknown_type');
    });
});

describe('ResolutionsExport - Styles', function () {
    it('applies bold style to header row', function () {
        $export = new ResolutionsExport([]);
        $sheet = \Mockery::mock(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class);

        $styles = $export->styles($sheet);

        expect($styles)->toHaveKey(1)
            ->and($styles[1])->toHaveKey('font')
            ->and($styles[1]['font'])->toHaveKey('bold')
            ->and($styles[1]['font']['bold'])->toBeTrue();
    });
});

describe('ResolutionsExport - Data Formatting', function () {
    it('formats dates correctly', function () {
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
            'official_date' => now()->setDate(2024, 6, 1),
            'published_at' => now()->setDate(2024, 6, 2)->setTime(10, 30),
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mapped = $export->map($resolution);

        expect($mapped[7])->toBe('01/06/2024')
            ->and($mapped[9])->toBe('02/06/2024 10:30');
    });

    it('formats resolution types correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $types = ['provisional', 'definitivo', 'alegaciones'];

        foreach ($types as $type) {
            $resolution = Resolution::factory()->create([
                'call_id' => $call->id,
                'call_phase_id' => $phase->id,
                'type' => $type,
            ]);

            $export = new ResolutionsExport(['call_id' => $call->id]);
            $mapped = $export->map($resolution);

            expect($mapped[4])->not->toBe($type); // Should be translated
        }
    });

    it('formats published status correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();
        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $phase = CallPhase::factory()->create(['call_id' => $call->id]);

        $published = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => now(),
        ]);
        $unpublished = Resolution::factory()->create([
            'call_id' => $call->id,
            'call_phase_id' => $phase->id,
            'published_at' => null,
        ]);

        $export = new ResolutionsExport(['call_id' => $call->id]);
        $mappedPublished = $export->map($published);
        $mappedUnpublished = $export->map($unpublished);

        expect($mappedPublished[8])->toBe(__('common.messages.yes'))
            ->and($mappedUnpublished[8])->toBe(__('common.messages.no'));
    });
});
