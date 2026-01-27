<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\createCallShowTestData;
use function Tests\Browser\Helpers\createHomeTestData;
use function Tests\Browser\Helpers\createNewsShowTestData;
use function Tests\Browser\Helpers\createProgramShowTestData;

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
// Tests: Tiempos de carga para páginas de detalle públicas
// ============================================

it('loads program show page within acceptable time', function () {
    $data = createProgramShowTestData();
    $program = $data['program'];

    $startTime = microtime(true);
    $page = visit(route('programas.show', $program));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('loads call show page within acceptable time', function () {
    $data = createCallShowTestData();
    $call = $data['call'];

    $startTime = microtime(true);
    $page = visit(route('convocatorias.show', $call));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2.5 segundos (2500ms) - más complejo por fases y resoluciones
    expect($loadTime)->toBeLessThan(2500);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('loads news show page within acceptable time', function () {
    $data = createNewsShowTestData();
    $newsPost = $data['newsPost'];

    $startTime = microtime(true);
    $page = visit(route('noticias.show', $newsPost));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee($newsPost->title)
        ->assertNoJavascriptErrors();
});

it('loads document show page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $document = Document::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'is_active' => true,
    ]);

    $startTime = microtime(true);
    $page = visit(route('documentos.show', $document));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee($document->title)
        ->assertNoJavascriptErrors();
});

it('loads event show page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $call = Call::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
    ]);

    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(7),
        'call_id' => $call->id,
    ]);

    $startTime = microtime(true);
    $page = visit(route('eventos.show', $event));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee($event->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Tests: Tiempos de carga con diferentes volúmenes de datos
// ============================================

it('loads programs index page with 10 programs within acceptable time', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    $startTime = microtime(true);
    $page = visit(route('programas.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 1.5 segundos (1500ms)
    expect($loadTime)->toBeLessThan(1500);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('loads programs index page with 50 programs within acceptable time', function () {
    Program::factory()->count(50)->create(['is_active' => true]);

    $startTime = microtime(true);
    $page = visit(route('programas.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2 segundos (2000ms)
    expect($loadTime)->toBeLessThan(2000);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('loads programs index page with 100 programs within acceptable time', function () {
    Program::factory()->count(100)->create(['is_active' => true]);

    $startTime = microtime(true);
    $page = visit(route('programas.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 3 segundos (3000ms)
    expect($loadTime)->toBeLessThan(3000);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('loads calls index page with 10 calls within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $startTime = microtime(true);
    $page = visit(route('convocatorias.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 1.5 segundos (1500ms)
    expect($loadTime)->toBeLessThan(1500);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('loads calls index page with 50 calls within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(50)->create([
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

it('loads calls index page with 100 calls within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(100)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $startTime = microtime(true);
    $page = visit(route('convocatorias.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 3 segundos (3000ms)
    expect($loadTime)->toBeLessThan(3000);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('loads news index page with 10 news posts within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(10)->create([
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

    // Verificar que la página carga en menos de 1.5 segundos (1500ms)
    expect($loadTime)->toBeLessThan(1500);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('loads news index page with 50 news posts within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(50)->create([
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

it('loads news index page with 100 news posts within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(100)->create([
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

    // Verificar que la página carga en menos de 3 segundos (3000ms)
    expect($loadTime)->toBeLessThan(3000);

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
