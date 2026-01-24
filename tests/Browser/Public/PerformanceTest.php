<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\createHomeTestData;

// ============================================
// Test: Verificar tiempos de carga
// ============================================

it('loads home page within acceptable time', function () {
    createHomeTestData();

    $startTime = microtime(true);
    $page = visit(route('home'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('loads programs index page within acceptable time', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    $startTime = microtime(true);
    $page = visit(route('programas.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('loads calls index page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $startTime = microtime(true);
    $page = visit(route('convocatorias.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('loads news index page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $startTime = microtime(true);
    $page = visit(route('noticias.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar número de consultas
// ============================================

it('executes reasonable number of queries on home page', function () {
    createHomeTestData();

    \DB::enableQueryLog();

    $page = visit(route('home'));

    $queries = \DB::getQueryLog();
    $queryCount = count($queries);

    \DB::disableQueryLog();

    // Verificar que no hay más de 20 consultas (número razonable para una página con relaciones)
    expect($queryCount)->toBeLessThan(20);

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('executes reasonable number of queries on programs index page', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    \DB::enableQueryLog();

    $page = visit(route('programas.index'));

    $queries = \DB::getQueryLog();
    $queryCount = count($queries);

    \DB::disableQueryLog();

    // Verificar que no hay más de 15 consultas
    expect($queryCount)->toBeLessThan(15);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('executes reasonable number of queries on calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    \DB::enableQueryLog();

    $page = visit(route('convocatorias.index'));

    $queries = \DB::getQueryLog();
    $queryCount = count($queries);

    \DB::disableQueryLog();

    // Verificar que no hay más de 20 consultas
    expect($queryCount)->toBeLessThan(20);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('executes reasonable number of queries on news index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    \DB::enableQueryLog();

    $page = visit(route('noticias.index'));

    $queries = \DB::getQueryLog();
    $queryCount = count($queries);

    \DB::disableQueryLog();

    // Verificar que no hay más de 20 consultas
    expect($queryCount)->toBeLessThan(20);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});
