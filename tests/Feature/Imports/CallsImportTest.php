<?php

use App\Imports\CallsImport;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    // Create test data
    $this->program = Program::factory()->create([
        'code' => 'KA131',
        'name' => 'Programa Test',
    ]);

    $this->academicYear = AcademicYear::factory()->create([
        'year' => '2024-2025',
    ]);
});

describe('CallsImport - Basic Import', function () {
    it('imports a single valid call successfully', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Slug',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
                'Fecha Inicio Estimada',
                'Fecha Fin Estimada',
                'Requisitos',
                'Documentación',
                'Criterios de Selección',
                'Estado',
                'Fecha Publicación',
                'Fecha Cierre',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                '',
                'alumnado',
                'corta',
                20,
                'Francia, Alemania',
                '2024-09-01',
                '2025-06-30',
                'Requisitos básicos',
                'Documentación necesaria',
                'Criterios aplicables',
                'borrador',
                '',
                '',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(1)
            ->and($import->getFailedCount())->toBe(0)
            ->and(Call::count())->toBe(1);

        $call = Call::first();
        expect($call)->not->toBeNull()
            ->and($call->title)->toBe('Convocatoria Test')
            ->and($call->program_id)->toBe($this->program->id)
            ->and($call->academic_year_id)->toBe($this->academicYear->id)
            ->and($call->type)->toBe('alumnado')
            ->and($call->modality)->toBe('corta')
            ->and($call->number_of_places)->toBe(20)
            ->and($call->destinations)->toBe(['Francia', 'Alemania']);

        // Verify created_by and updated_by are set
        expect($call->created_by)->toBe($this->user->id)
            ->and($call->updated_by)->toBe($this->user->id);
    });

    it('generates slug automatically when not provided', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Slug',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Sin Slug',
                '', // Slug vacío
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->slug)->toBe('convocatoria-sin-slug');
    });

    it('uses provided slug when available', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Slug',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'slug-personalizado',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->slug)->toBe('slug-personalizado');
    });

    it('assigns created_by and updated_by to current user', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call)->not->toBeNull()
            ->and($call->created_by)->toBe($this->user->id)
            ->and($call->updated_by)->toBe($this->user->id);
    });

    it('converts destinations string to array', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'Francia, Alemania, Italia',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->destinations)->toBe(['Francia', 'Alemania', 'Italia']);
    });

    it('handles destinations separated by semicolon', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'Francia; Alemania; Italia',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->destinations)->toBe(['Francia', 'Alemania', 'Italia']);
    });
});

describe('CallsImport - Validation Errors', function () {
    it('fails when program does not exist', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'PROGRAMA_INEXISTENTE',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and($import->getRowErrors()->first()['row'])->toBe(2)
            ->and(Call::count())->toBe(0);
    });

    it('fails when academic year does not exist', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2020-2021', // Año que no existe
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when type is invalid', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'tipo_invalido', // Tipo inválido
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when modality is invalid', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'modalidad_invalida', // Modalidad inválida
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when required fields are missing', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                '', // Título vacío
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when number of places is invalid', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                0, // Número de plazas inválido (debe ser >= 1)
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when destinations is empty', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                '', // Destinos vacíos
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });

    it('fails when end date is before start date', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
                'Fecha Inicio Estimada',
                'Fecha Fin Estimada',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
                '2025-06-30',
                '2024-09-01', // Fecha fin antes de fecha inicio
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0);
    });
});

describe('CallsImport - Date Parsing', function () {
    it('parses dates in Y-m-d format', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
                'Fecha Inicio Estimada',
                'Fecha Fin Estimada',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
                '2024-09-01',
                '2025-06-30',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->estimated_start_date->format('Y-m-d'))->toBe('2024-09-01')
            ->and($call->estimated_end_date->format('Y-m-d'))->toBe('2025-06-30');
    });

    it('parses dates in d/m/Y format', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
                'Fecha Inicio Estimada',
                'Fecha Fin Estimada',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
                '01/09/2024',
                '30/06/2025',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->estimated_start_date->format('Y-m-d'))->toBe('2024-09-01')
            ->and($call->estimated_end_date->format('Y-m-d'))->toBe('2025-06-30');
    });
});

describe('CallsImport - Multiple Rows', function () {
    it('imports multiple valid calls', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria 1',
                'alumnado',
                'corta',
                10,
                'España',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria 2',
                'personal',
                'larga',
                5,
                'Francia',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(2)
            ->and($import->getFailedCount())->toBe(0)
            ->and(Call::count())->toBe(2);

        $calls = Call::orderBy('title')->get();
        expect($calls[0]->title)->toBe('Convocatoria 1')
            ->and($calls[1]->title)->toBe('Convocatoria 2');
    });

    it('continues processing when some rows fail', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Válida',
                'alumnado',
                'corta',
                10,
                'España',
            ],
            [
                'PROGRAMA_INEXISTENTE',
                '2024-2025',
                'Convocatoria Inválida',
                'alumnado',
                'corta',
                10,
                'España',
            ],
            [
                'KA131',
                '2024-2025',
                'Otra Convocatoria Válida',
                'personal',
                'larga',
                5,
                'Francia',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        expect($import->getImportedCount())->toBe(2)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(2);

        $calls = Call::orderBy('title')->get();
        expect($calls->pluck('title')->toArray())->toBe(['Convocatoria Válida', 'Otra Convocatoria Válida']);
    });
});

describe('CallsImport - Dry Run Mode', function () {
    it('validates without saving in dry-run mode', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(true, $this->user->id); // dry-run = true
        Excel::import($import, $file);

        expect($import->getValidatedCount())->toBe(1)
            ->and($import->getFailedCount())->toBe(0)
            ->and(Call::count())->toBe(0); // No se guardó nada
    });

    it('reports validation errors in dry-run mode', function () {
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'PROGRAMA_INEXISTENTE',
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(true, $this->user->id); // dry-run = true
        Excel::import($import, $file);

        expect($import->getValidatedCount())->toBe(0)
            ->and($import->getFailedCount())->toBe(1)
            ->and(Call::count())->toBe(0); // No se guardó nada
    });
});

describe('CallsImport - Program and Academic Year Lookup', function () {
    it('finds program by code', function () {
        // Use existing program from beforeEach
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131', // Buscar por código
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->program_id)->toBe($this->program->id);
    });

    it('finds program by name', function () {
        // Use existing program from beforeEach
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'Programa Test', // Buscar por nombre
                '2024-2025',
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->program_id)->toBe($this->program->id);
    });

    it('finds academic year by year value', function () {
        // Use existing academic year from beforeEach
        $data = [
            [
                'Programa',
                'Año Académico',
                'Título',
                'Tipo',
                'Modalidad',
                'Número de Plazas',
                'Destinos',
            ],
            [
                'KA131',
                '2024-2025', // Buscar por año
                'Convocatoria Test',
                'alumnado',
                'corta',
                10,
                'España',
            ],
        ];

        $file = createExcelFile($data);

        $import = new CallsImport(false, $this->user->id);
        Excel::import($import, $file);

        $call = Call::first();
        expect($call->academic_year_id)->toBe($this->academicYear->id);
    });
});
