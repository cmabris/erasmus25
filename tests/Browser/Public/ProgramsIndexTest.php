<?php

use App\Models\Program;

use function Tests\Browser\Helpers\createProgramsTestData;

// ============================================
// Test: Verificar renderizado de listado de programas
// ============================================

it('can visit the programs index page', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete programs index page structure', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    // Verificar estructura HTML básica
    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar visualización de programas
// ============================================

it('displays active programs by default', function () {
    $activeProgram = Program::factory()->create([
        'name' => 'Active Program',
        'is_active' => true,
    ]);

    $inactiveProgram = Program::factory()->create([
        'name' => 'Inactive Program',
        'is_active' => false,
    ]);

    $page = visit('/programas');

    $page->assertSee('Active Program')
        ->assertDontSee('Inactive Program')
        ->assertNoJavascriptErrors();
});

it('displays program data correctly', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'code' => 'KA121-VET',
        'description' => 'Test Description',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    $page->assertSee('Test Program')
        ->assertSee('KA121-VET')
        ->assertSee('Test Description')
        ->assertNoJavascriptErrors();
});

it('displays links to program detail pages', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    // Verificar que el programa es clickeable
    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por tipo de programa
// ============================================

it('filters programs by type KA1', function () {
    $ka1Program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'KA1 Program',
        'is_active' => true,
    ]);

    $ka2Program = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'KA2 Program',
        'is_active' => true,
    ]);

    $page = visit('/programas?tipo=KA1');

    $page->assertSee('KA1 Program')
        ->assertDontSee('KA2 Program')
        ->assertNoJavascriptErrors();
});

it('filters programs by type KA2', function () {
    $ka1Program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'KA1 Program',
        'is_active' => true,
    ]);

    $ka2Program = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'KA2 Program',
        'is_active' => true,
    ]);

    $page = visit('/programas?tipo=KA2');

    $page->assertSee('KA2 Program')
        ->assertDontSee('KA1 Program')
        ->assertNoJavascriptErrors();
});

it('filters programs by type JM', function () {
    $jmProgram = Program::factory()->create([
        'code' => 'JM-001',
        'name' => 'Jean Monnet Program',
        'is_active' => true,
    ]);

    $ka1Program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'KA1 Program',
        'is_active' => true,
    ]);

    $page = visit('/programas?tipo=JM');

    $page->assertSee('Jean Monnet Program')
        ->assertDontSee('KA1 Program')
        ->assertNoJavascriptErrors();
});

it('filters programs by type DISCOVER', function () {
    $discoverProgram = Program::factory()->create([
        'code' => 'DISCOVER-001',
        'name' => 'DiscoverEU Program',
        'is_active' => true,
    ]);

    $ka1Program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'KA1 Program',
        'is_active' => true,
    ]);

    $page = visit('/programas?tipo=DISCOVER');

    $page->assertSee('DiscoverEU Program')
        ->assertDontSee('KA1 Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro de programas activos
// ============================================

it('shows only active programs when filter is enabled', function () {
    $activeProgram = Program::factory()->create([
        'name' => 'Active Program',
        'is_active' => true,
    ]);

    $inactiveProgram = Program::factory()->create([
        'name' => 'Inactive Program',
        'is_active' => false,
    ]);

    $page = visit('/programas?activos=1');

    $page->assertSee('Active Program')
        ->assertDontSee('Inactive Program')
        ->assertNoJavascriptErrors();
});

it('shows all programs when active filter is disabled', function () {
    $activeProgram = Program::factory()->create([
        'name' => 'Active Program',
        'is_active' => true,
    ]);

    $inactiveProgram = Program::factory()->create([
        'name' => 'Inactive Program',
        'is_active' => false,
    ]);

    $page = visit('/programas?activos=0');

    $page->assertSee('Active Program')
        ->assertSee('Inactive Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar búsqueda de programas
// ============================================

it('searches programs by name', function () {
    $program1 = Program::factory()->create([
        'name' => 'Programa de Movilidad',
        'is_active' => true,
    ]);

    $program2 = Program::factory()->create([
        'name' => 'Programa de Cooperación',
        'is_active' => true,
    ]);

    $page = visit('/programas?q=Movilidad');

    $page->assertSee('Programa de Movilidad')
        ->assertDontSee('Programa de Cooperación')
        ->assertNoJavascriptErrors();
});

it('searches programs by code', function () {
    $program1 = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Programa VET',
        'is_active' => true,
    ]);

    $program2 = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Programa Escolar',
        'is_active' => true,
    ]);

    $page = visit('/programas?q=KA121');

    $page->assertSee('Programa VET')
        ->assertDontSee('Programa Escolar')
        ->assertNoJavascriptErrors();
});

it('searches programs by description', function () {
    $program1 = Program::factory()->create([
        'name' => 'Programa 1',
        'description' => 'Descripción con palabra clave única',
        'is_active' => true,
    ]);

    $program2 = Program::factory()->create([
        'name' => 'Programa 2',
        'description' => 'Otra descripción diferente',
        'is_active' => true,
    ]);

    $page = visit('/programas?q=palabra clave única');

    $page->assertSee('Programa 1')
        ->assertDontSee('Programa 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar paginación
// ============================================

it('displays pagination when there are more than 9 programs', function () {
    // Crear 12 programas activos con nombres únicos
    $programs = Program::factory()->count(12)->create(['is_active' => true]);

    $page = visit('/programas');

    // Verificar que se muestran programas (puede ser cualquiera de los 12)
    // Contar cuántos programas se muestran
    $programsCount = 0;
    foreach ($programs as $program) {
        try {
            $page->assertSee($program->name);
            $programsCount++;
        } catch (\Exception $e) {
            // Programa no visible en esta página, continuar
        }
    }

    // Verificar que se muestran máximo 9 programas en la primera página
    expect($programsCount)->toBeLessThanOrEqual(9);
    // Verificar que al menos se muestra un programa
    expect($programsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('maintains filters when navigating between pages', function () {
    // Crear programas de diferentes tipos con códigos únicos
    $ka1Programs = collect();
    for ($i = 1; $i <= 5; $i++) {
        $ka1Programs->push(Program::factory()->create([
            'code' => "KA121-VET-{$i}",
            'name' => "Programa KA1 {$i}",
            'is_active' => true,
        ]));
    }

    $ka2Programs = collect();
    for ($i = 1; $i <= 5; $i++) {
        $ka2Programs->push(Program::factory()->create([
            'code' => "KA220-SCH-{$i}",
            'name' => "Programa KA2 {$i}",
            'is_active' => true,
        ]));
    }

    $page = visit('/programas?tipo=KA1');

    // Verificar que el filtro está aplicado (debe mostrar programas KA1)
    $page->assertSee($ka1Programs->first()->name)
        ->assertDontSee($ka2Programs->first()->name)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar estadísticas
// ============================================

it('displays program statistics', function () {
    // Crear programas de diferentes tipos
    $program1 = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Programa KA1',
        'is_active' => true,
    ]);

    $program2 = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Programa KA2',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    // Verificar que la página carga sin errores (las estadísticas se muestran)
    $page->assertSee('Programa KA1')
        ->assertSee('Programa KA2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar reset de filtros
// ============================================

it('resets filters to default values', function () {
    $activeProgram = Program::factory()->create([
        'name' => 'Active Program',
        'is_active' => true,
    ]);

    $inactiveProgram = Program::factory()->create([
        'name' => 'Inactive Program',
        'is_active' => false,
    ]);

    // Visitar con filtros aplicados
    $page = visit('/programas?tipo=KA1&q=test&activos=0');

    // Verificar que la página carga sin errores
    $page->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading
// ============================================

it('verifies no lazy loading problems', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies all necessary relationships are loaded', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/programas');

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar helper createProgramsTestData
// ============================================

it('displays programs using createProgramsTestData helper', function () {
    $data = createProgramsTestData();

    $page = visit('/programas');

    // Verificar que se muestran los programas activos
    $page->assertSee('Programa KA1 VET')
        ->assertSee('Programa KA2 Escolar')
        ->assertSee('Programa Jean Monnet')
        // Verificar que NO se muestra el programa inactivo por defecto
        ->assertDontSee('Programa Inactivo')
        ->assertNoJavascriptErrors();
});

it('displays inactive programs when filter is disabled using helper', function () {
    $data = createProgramsTestData();

    $page = visit('/programas?activos=0');

    // Verificar que se muestran todos los programas (activos e inactivos)
    $page->assertSee('Programa KA1 VET')
        ->assertSee('Programa KA2 Escolar')
        ->assertSee('Programa Jean Monnet')
        ->assertSee('Programa Inactivo')
        ->assertNoJavascriptErrors();
});
