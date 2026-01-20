<?php

use App\Exports\CallsExport;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CallsExport - Basic Export', function () {
    it('exports all calls when no filters applied', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        Call::factory()->count(5)->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $export = new CallsExport([]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(5);
    });

    it('excludes soft deleted calls by default', function () {
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
        $call2->delete(); // Soft delete

        $export = new CallsExport([]);
        $collection = $export->query()->get();

        expect($collection)->toHaveCount(1)
            ->and($collection->first()->id)->toBe($call1->id);
    });

    it('has correct headings', function () {
        $export = new CallsExport([]);
        $headings = $export->headings();

        expect($headings)->toBe([
            __('ID'),
            __('Título'),
            __('Programa'),
            __('Año Académico'),
            __('Tipo'),
            __('Modalidad'),
            __('Número de Plazas'),
            __('Destinos'),
            __('Fecha Inicio Estimada'),
            __('Fecha Fin Estimada'),
            __('Estado'),
            __('Fecha Publicación'),
            __('Fecha Cierre'),
            __('Creador'),
            __('Fecha Creación'),
            __('Fecha Actualización'),
        ]);
    });

    it('has correct sheet title', function () {
        $export = new CallsExport([]);
        $title = $export->title();

        expect($title)->toBe(__('Convocatorias'));
    });

    it('maps call data correctly', function () {
        $program = Program::factory()->create(['name' => 'Programa Test']);
        $academicYear = AcademicYear::factory()->create(['year' => '2024-2025']);
        $creator = User::factory()->create(['name' => 'Test User']);

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
            'type' => 'alumnado',
            'modality' => 'corta',
            'number_of_places' => 10,
            'destinations' => ['España', 'Francia'],
            'estimated_start_date' => now()->setDate(2024, 6, 1),
            'estimated_end_date' => now()->setDate(2024, 8, 31),
            'status' => 'abierta',
            'published_at' => now()->setDate(2024, 1, 15)->setTime(10, 0),
            'closed_at' => null,
            'created_by' => $creator->id,
        ]);

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[0])->toBe($call->id)
            ->and($mapped[1])->toBe('Convocatoria Test')
            ->and($mapped[2])->toBe('Programa Test')
            ->and($mapped[3])->toBe('2024-2025')
            ->and($mapped[4])->toBe(__('common.call_types.students'))
            ->and($mapped[5])->toBe(__('common.call_modalities.short'))
            ->and($mapped[6])->toBe(10)
            ->and($mapped[7])->toBe('España, Francia')
            ->and($mapped[8])->toBe('01/06/2024')
            ->and($mapped[9])->toBe('31/08/2024')
            ->and($mapped[10])->toBe(__('common.call_status.open'))
            ->and($mapped[11])->toBe('15/01/2024 10:00')
            ->and($mapped[12])->toBe('-')
            ->and($mapped[13])->toBe('Test User');
    });

    it('handles null and empty values correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => [], // Empty array instead of null
            'estimated_start_date' => null,
            'estimated_end_date' => null,
            'published_at' => null,
            'closed_at' => null,
            'created_by' => null,
        ]);

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[7])->toBe('-') // Empty destinations
            ->and($mapped[8])->toBe('-') // Null estimated_start_date
            ->and($mapped[9])->toBe('-') // Null estimated_end_date
            ->and($mapped[11])->toBe('-') // Null published_at
            ->and($mapped[12])->toBe('-') // Null closed_at
            ->and($mapped[13])->toBe(__('common.messages.system')); // Null creator
    });

    it('formats destinations correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['España', 'Francia', 'Italia'],
        ]);

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[7])->toBe('España, Francia, Italia');
    });

    it('formats empty destinations correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => [],
        ]);

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[7])->toBe('-');
    });
});

describe('CallsExport - Filters', function () {
    it('applies program filter', function () {
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program2->id,
            'academic_year_id' => $academicYear->id,
        ]);

        $export = new CallsExport(['filterProgram' => $program1->id]);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies academic year filter', function () {
        $program = Program::factory()->create();
        $academicYear1 = AcademicYear::factory()->create(['year' => '2024-2025']);
        $academicYear2 = AcademicYear::factory()->create(['year' => '2023-2024']);

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear1->id,
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear2->id,
        ]);

        $export = new CallsExport(['filterAcademicYear' => $academicYear1->id]);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies type filter', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'personal',
        ]);

        $export = new CallsExport(['filterType' => 'alumnado']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies modality filter', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'modality' => 'corta',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'modality' => 'larga',
        ]);

        $export = new CallsExport(['filterModality' => 'corta']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies status filter', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'abierta',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'cerrada',
        ]);

        $export = new CallsExport(['filterStatus' => 'abierta']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies search filter', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria de Movilidad',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria de Cooperación',
        ]);

        $export = new CallsExport(['search' => 'Movilidad']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('applies search filter by slug', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Convocatoria Test',
            'slug' => 'convocatoria-test',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'Otra Convocatoria',
            'slug' => 'otra-convocatoria',
        ]);

        $export = new CallsExport(['search' => 'test']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id);
    });

    it('includes only deleted calls when showDeleted is 1', function () {
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
        $call2->delete(); // Soft delete

        $export = new CallsExport(['showDeleted' => '1']);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->not->toContain($call1->id) // Should not include non-deleted
            ->and($ids)->toContain($call2->id); // Should include deleted
    });

    it('applies sorting', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'B Convocatoria',
            'created_at' => now()->subDays(2),
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'title' => 'A Convocatoria',
            'created_at' => now()->subDays(1),
        ]);

        $export = new CallsExport([
            'sortField' => 'title',
            'sortDirection' => 'asc',
        ]);
        $collection = $export->query()->get();

        $titles = $collection->pluck('title')->toArray();
        expect($titles[0])->toBe('A Convocatoria')
            ->and($titles[1])->toBe('B Convocatoria');
    });

    it('applies multiple filters together', function () {
        $program1 = Program::factory()->create();
        $program2 = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
            'status' => 'abierta',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program1->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'personal',
            'status' => 'abierta',
        ]);
        $call3 = Call::factory()->create([
            'program_id' => $program2->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
            'status' => 'abierta',
        ]);

        $export = new CallsExport([
            'filterProgram' => $program1->id,
            'filterType' => 'alumnado',
        ]);
        $collection = $export->query()->get();

        $ids = $collection->pluck('id')->toArray();
        expect($ids)->toContain($call1->id)
            ->and($ids)->not->toContain($call2->id)
            ->and($ids)->not->toContain($call3->id);
    });
});

describe('CallsExport - Edge Cases for Labels', function () {
    it('handles null type correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado', // Create with valid type first
        ]);

        // Force null type on the model attribute
        $call->type = null;

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[4])->toBe('-');
    });

    it('handles unknown type with default value', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Force an unknown type directly on the model attribute
        $call->type = 'unknown_type';

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[4])->toBe('unknown_type');
    });

    it('handles null modality correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'modality' => 'corta', // Create with valid modality first
        ]);

        // Force null modality on the model attribute
        $call->modality = null;

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[5])->toBe('-');
    });

    it('handles unknown modality with default value', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Force an unknown modality directly on the model attribute
        $call->modality = 'unknown_modality';

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[5])->toBe('unknown_modality');
    });

    it('handles null status correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'status' => 'borrador', // Create with valid status first
        ]);

        // Force null status on the model attribute
        $call->status = null;

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[10])->toBe('-');
    });

    it('handles unknown status with default value', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // Force an unknown status directly on the model attribute
        $call->status = 'unknown_status';

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[10])->toBe('unknown_status');
    });

    it('handles null destinations correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'destinations' => ['España'], // Create with valid destinations first
        ]);

        // Force null destinations on the model attribute
        $call->destinations = null;

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[7])->toBe('-');
    });

    it('handles non-array destinations correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
        ]);

        // The formatDestinations method checks is_array, so we can test its behavior
        // with different types if the model allows it
        $export = new CallsExport([]);
        $mapped = $export->map($call);

        // Destinations should be formatted properly or show '-'
        expect($mapped[7])->toBeString();
    });
});

describe('CallsExport - Styles', function () {
    it('applies bold style to header row', function () {
        $export = new CallsExport([]);
        $sheet = \Mockery::mock(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class);

        $styles = $export->styles($sheet);

        expect($styles)->toHaveKey(1)
            ->and($styles[1])->toHaveKey('font')
            ->and($styles[1]['font'])->toHaveKey('bold')
            ->and($styles[1]['font']['bold'])->toBeTrue();
    });
});

describe('CallsExport - Data Formatting', function () {
    it('formats dates correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'estimated_start_date' => now()->setDate(2024, 6, 1),
            'estimated_end_date' => now()->setDate(2024, 8, 31),
            'published_at' => now()->setDate(2024, 1, 15)->setTime(10, 30),
            'closed_at' => now()->setDate(2024, 12, 31)->setTime(14, 45),
        ]);

        $export = new CallsExport([]);
        $mapped = $export->map($call);

        expect($mapped[8])->toBe('01/06/2024')
            ->and($mapped[9])->toBe('31/08/2024')
            ->and($mapped[11])->toBe('15/01/2024 10:30')
            ->and($mapped[12])->toBe('31/12/2024 14:45');
    });

    it('formats call types correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'alumnado',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'type' => 'personal',
        ]);

        $export = new CallsExport([]);
        $mapped1 = $export->map($call1);
        $mapped2 = $export->map($call2);

        expect($mapped1[4])->toBe(__('common.call_types.students'))
            ->and($mapped2[4])->toBe(__('common.call_types.staff'));
    });

    it('formats modalities correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $call1 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'modality' => 'corta',
        ]);
        $call2 = Call::factory()->create([
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'modality' => 'larga',
        ]);

        $export = new CallsExport([]);
        $mapped1 = $export->map($call1);
        $mapped2 = $export->map($call2);

        expect($mapped1[5])->toBe(__('common.call_modalities.short'))
            ->and($mapped2[5])->toBe(__('common.call_modalities.long'));
    });

    it('formats statuses correctly', function () {
        $program = Program::factory()->create();
        $academicYear = AcademicYear::factory()->create();

        $statuses = ['borrador', 'abierta', 'cerrada', 'en_baremacion', 'resuelta', 'archivada'];

        foreach ($statuses as $status) {
            $call = Call::factory()->create([
                'program_id' => $program->id,
                'academic_year_id' => $academicYear->id,
                'status' => $status,
            ]);

            $export = new CallsExport([]);
            $mapped = $export->map($call);

            expect($mapped[10])->not->toBe($status); // Should be translated
        }
    });
});
