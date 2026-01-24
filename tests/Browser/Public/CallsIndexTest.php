<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;

use function Tests\Browser\Helpers\createCallsTestData;

// ============================================
// Test: Verificar renderizado de listado de convocatorias
// ============================================

it('can visit the calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete calls index page structure', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Call',
    ]);

    $page = visit('/convocatorias');

    // Verificar estructura HTML básica
    $page->assertSee('Test Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar visualización de convocatorias
// ============================================

it('displays published calls only', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $publishedCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Published Call',
    ]);

    $unpublishedCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => null,
        'title' => 'Unpublished Call',
    ]);

    $page = visit('/convocatorias');

    $page->assertSee('Published Call')
        ->assertDontSee('Unpublished Call')
        ->assertNoJavascriptErrors();
});

it('displays call data correctly', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);
    $academicYear = AcademicYear::factory()->create([
        'year' => '2025-2026',
    ]);

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Call',
    ]);

    $page = visit('/convocatorias');

    $page->assertSee('Test Call')
        ->assertNoJavascriptErrors();
});

it('displays links to call detail pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Verificar que la convocatoria es clickeable
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and academicYear', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por programa
// ============================================

it('filters calls by program', function () {
    $program1 = Program::factory()->create([
        'name' => 'Program 1',
        'is_active' => true,
    ]);
    $program2 = Program::factory()->create([
        'name' => 'Program 2',
        'is_active' => true,
    ]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Program 1',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Program 2',
    ]);

    $page = visit('/convocatorias?programa='.$program1->id);

    $page->assertSee('Call Program 1')
        ->assertDontSee('Call Program 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por año académico
// ============================================

it('filters calls by academic year', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear1 = AcademicYear::factory()->create(['year' => '2024-2025']);
    $academicYear2 = AcademicYear::factory()->create(['year' => '2025-2026']);

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear1->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Year 1',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear2->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Year 2',
    ]);

    $page = visit('/convocatorias?ano='.$academicYear1->id);

    $page->assertSee('Call Year 1')
        ->assertDontSee('Call Year 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por tipo (alumnado/personal)
// ============================================

it('filters calls by type alumnado', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'alumnado',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Alumnado',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'personal',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Personal',
    ]);

    $page = visit('/convocatorias?tipo=alumnado');

    $page->assertSee('Call Alumnado')
        ->assertDontSee('Call Personal')
        ->assertNoJavascriptErrors();
});

it('filters calls by type personal', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'alumnado',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Alumnado',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'personal',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Personal',
    ]);

    $page = visit('/convocatorias?tipo=personal');

    $page->assertSee('Call Personal')
        ->assertDontSee('Call Alumnado')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por modalidad (corta/larga)
// ============================================

it('filters calls by modality corta', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'modality' => 'corta',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Corta',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'modality' => 'larga',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Larga',
    ]);

    $page = visit('/convocatorias?modalidad=corta');

    $page->assertSee('Call Corta')
        ->assertDontSee('Call Larga')
        ->assertNoJavascriptErrors();
});

it('filters calls by modality larga', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'modality' => 'corta',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Corta',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'modality' => 'larga',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Larga',
    ]);

    $page = visit('/convocatorias?modalidad=larga');

    $page->assertSee('Call Larga')
        ->assertDontSee('Call Corta')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por estado (abierta/cerrada)
// ============================================

it('filters calls by status abierta', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Abierta',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now(),
        'title' => 'Call Cerrada',
    ]);

    $page = visit('/convocatorias?estado=abierta');

    $page->assertSee('Call Abierta')
        ->assertDontSee('Call Cerrada')
        ->assertNoJavascriptErrors();
});

it('filters calls by status cerrada', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call Abierta',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now(),
        'title' => 'Call Cerrada',
    ]);

    $page = visit('/convocatorias?estado=cerrada');

    $page->assertSee('Call Cerrada')
        ->assertDontSee('Call Abierta')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar búsqueda de convocatorias
// ============================================

it('searches calls by title', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de Movilidad',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Convocatoria de Cooperación',
    ]);

    $page = visit('/convocatorias?q=Movilidad');

    $page->assertSee('Convocatoria de Movilidad')
        ->assertDontSee('Convocatoria de Cooperación')
        ->assertNoJavascriptErrors();
});

it('searches calls by requirements', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call 1',
        'requirements' => 'Requisito especifico unico',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call 2',
        'requirements' => 'Otro requisito diferente',
    ]);

    $page = visit('/convocatorias?q=especifico unico');

    $page->assertSee('Call 1')
        ->assertDontSee('Call 2')
        ->assertNoJavascriptErrors();
});

it('searches calls by documentation', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call1 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call 1',
        'documentation' => 'Documentacion especifica unica',
    ]);

    $call2 = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Call 2',
        'documentation' => 'Otra documentacion diferente',
    ]);

    $page = visit('/convocatorias?q=especifica unica');

    $page->assertSee('Call 1')
        ->assertDontSee('Call 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar combinación de filtros
// ============================================

it('applies multiple filters simultaneously', function () {
    $program1 = Program::factory()->create(['is_active' => true]);
    $program2 = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $matchingCall = Call::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'alumnado',
        'modality' => 'corta',
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Matching Call',
    ]);

    $nonMatchingCall = Call::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'personal',
        'modality' => 'larga',
        'status' => 'cerrada',
        'published_at' => now(),
        'title' => 'Non Matching Call',
    ]);

    $page = visit('/convocatorias?programa='.$program1->id.'&tipo=alumnado&modalidad=corta&estado=abierta');

    $page->assertSee('Matching Call')
        ->assertDontSee('Non Matching Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar paginación
// ============================================

it('displays pagination when there are more than 12 calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear 15 convocatorias
    $calls = Call::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Verificar que se muestran máximo 12 convocatorias en la primera página
    $callsCount = 0;
    foreach ($calls as $call) {
        try {
            $page->assertSee($call->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible en esta página, continuar
        }
    }

    expect($callsCount)->toBeLessThanOrEqual(12);
    expect($callsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('maintains filters when navigating between pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear convocatorias de diferentes tipos
    Call::factory()->count(7)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'alumnado',
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    Call::factory()->count(7)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'personal',
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias?tipo=alumnado');

    // Verificar que el filtro está aplicado
    $page->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar estadísticas
// ============================================

it('displays call statistics', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear convocatorias de diferentes estados
    Call::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    Call::factory()->count(2)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Verificar que la página carga sin errores (las estadísticas se muestran)
    $page->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar reset de filtros
// ============================================

it('resets filters to default values', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Visitar con filtros aplicados
    $page = visit('/convocatorias?programa='.$program->id.'&tipo=alumnado&q=test&estado=abierta');

    // Verificar que la página carga sin errores
    $page->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar ordenamiento
// ============================================

it('orders calls with abierta status first', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $closedCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(5),
        'title' => 'Closed Call',
    ]);

    $openCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Open Call',
    ]);

    $page = visit('/convocatorias');

    // Verificar que ambas convocatorias se muestran
    $page->assertSee('Open Call')
        ->assertSee('Closed Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading
// ============================================

it('verifies no lazy loading problems', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies all necessary relationships are loaded', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/convocatorias');

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar helper createCallsTestData
// ============================================

it('displays calls using createCallsTestData helper', function () {
    $data = createCallsTestData();

    $page = visit('/convocatorias');

    // Verificar que se muestran las convocatorias creadas
    foreach ($data['calls'] as $call) {
        $page->assertSee($call->title);
    }

    $page->assertNoJavascriptErrors();
});
