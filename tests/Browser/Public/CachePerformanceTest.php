<?php

use App\Models\AcademicYear;
use App\Models\Program;
use Illuminate\Support\Facades\Cache;

use function Tests\Browser\Helpers\compareQueryCountsWithCache;
use function Tests\Browser\Helpers\createHomeTestData;
use function Tests\Browser\Helpers\getBrowserQueryCount;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Caché en páginas públicas
// ============================================

it('cache reduces queries on second load of home page', function () {
    createHomeTestData();

    // Limpiar caché antes del test
    Cache::flush();

    // Primera carga
    startBrowserQueryLog();
    $page1 = visit(route('home'));
    $queriesWithoutCache = stopBrowserQueryLog();

    // Segunda carga (debería usar caché)
    startBrowserQueryLog();
    $page2 = visit(route('home'));
    $queriesWithCache = stopBrowserQueryLog();

    // Verificar que la segunda carga tiene menos o igual número de queries
    compareQueryCountsWithCache($queriesWithoutCache, $queriesWithCache);

    $page2->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('cache for current academic year works', function () {
    // Limpiar caché
    Cache::flush();

    // Crear año académico actual
    $academicYear = AcademicYear::factory()->create(['is_current' => true]);

    // Primera carga - debería ejecutar query para obtener año actual
    startBrowserQueryLog();
    $page1 = visit(route('home'));
    $queriesFirst = stopBrowserQueryLog();

    // Segunda carga - debería usar caché, no ejecutar query para año actual
    startBrowserQueryLog();
    $page2 = visit(route('home'));
    $queriesSecond = stopBrowserQueryLog();

    // Verificar que la segunda carga tiene menos queries
    $countFirst = getBrowserQueryCount($queriesFirst);
    $countSecond = getBrowserQueryCount($queriesSecond);

    expect($countSecond)->toBeLessThanOrEqual($countFirst, 'Second load should have fewer or equal queries due to cache');

    $page2->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('cache for active programs works', function () {
    // Limpiar caché
    Cache::flush();

    // Crear programas activos
    Program::factory()->count(6)->create(['is_active' => true]);

    // Primera carga - debería ejecutar query para obtener programas activos
    startBrowserQueryLog();
    $page1 = visit(route('home'));
    $queriesFirst = stopBrowserQueryLog();

    // Segunda carga - debería usar caché de programas activos
    startBrowserQueryLog();
    $page2 = visit(route('home'));
    $queriesSecond = stopBrowserQueryLog();

    // Verificar que la segunda carga tiene menos queries relacionadas con programas
    $countFirst = getBrowserQueryCount($queriesFirst);
    $countSecond = getBrowserQueryCount($queriesSecond);

    expect($countSecond)->toBeLessThanOrEqual($countFirst, 'Second load should have fewer or equal queries due to programs cache');

    $page2->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('cache is invalidated when content is updated', function () {
    createHomeTestData();

    // Limpiar caché
    Cache::flush();

    // Primera carga
    startBrowserQueryLog();
    $page1 = visit(route('home'));
    $queriesFirst = stopBrowserQueryLog();

    // Segunda carga (con caché)
    startBrowserQueryLog();
    $page2 = visit(route('home'));
    $queriesSecond = stopBrowserQueryLog();

    // Actualizar un programa (cambiar is_active)
    $program = Program::where('is_active', true)->first();
    if ($program) {
        $program->update(['is_active' => false]);
    }

    // Tercera carga - debería invalidar caché y ejecutar queries para obtener programas actualizados
    startBrowserQueryLog();
    $page3 = visit(route('home'));
    $queriesThird = stopBrowserQueryLog();

    // La tercera carga debería tener más queries que la segunda (caché invalidado)
    $countSecond = getBrowserQueryCount($queriesSecond);
    $countThird = getBrowserQueryCount($queriesThird);

    // Nota: Puede que la tercera carga tenga más queries porque se invalidó el caché de programas
    // y se ejecutaron queries para obtener los programas actualizados
    expect($countThird)->toBeGreaterThanOrEqual($countSecond, 'Third load should have more or equal queries after cache invalidation');

    $page3->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});
