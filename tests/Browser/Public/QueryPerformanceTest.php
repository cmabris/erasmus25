<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\assertBrowserNoDuplicateQueries;
use function Tests\Browser\Helpers\assertBrowserQueryCountLessThan;
use function Tests\Browser\Helpers\createCallShowTestData;
use function Tests\Browser\Helpers\createGlobalSearchTestData;
use function Tests\Browser\Helpers\createHomeTestData;
use function Tests\Browser\Helpers\createNewsShowTestData;
use function Tests\Browser\Helpers\createProgramShowTestData;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Número máximo de consultas para páginas públicas
// ============================================

it('executes less than 20 queries on home page', function () {
    createHomeTestData();

    startBrowserQueryLog();
    $page = visit(route('home'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 20);

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('executes less than 15 queries on programs index page', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    startBrowserQueryLog();
    $page = visit(route('programas.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 15);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('executes less than 25 queries on program show page', function () {
    $data = createProgramShowTestData();
    $program = $data['program'];

    startBrowserQueryLog();
    $page = visit(route('programas.show', $program));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 25);

    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('executes less than 20 queries on calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    startBrowserQueryLog();
    $page = visit(route('convocatorias.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 20);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('executes less than 25 queries on call show page', function () {
    $data = createCallShowTestData();
    $call = $data['call'];

    startBrowserQueryLog();
    $page = visit(route('convocatorias.show', $call));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 25);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('executes less than 20 queries on news index page', function () {
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

    startBrowserQueryLog();
    $page = visit(route('noticias.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 20);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('executes less than 25 queries on news show page', function () {
    $data = createNewsShowTestData();
    $newsPost = $data['newsPost'];

    startBrowserQueryLog();
    $page = visit(route('noticias.show', $newsPost));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 25);

    $page->assertSee($newsPost->title)
        ->assertNoJavascriptErrors();
});

it('executes less than 40 queries on global search page', function () {
    createGlobalSearchTestData();

    startBrowserQueryLog();
    $page = visit(route('search', ['q' => 'Movilidad']));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 40);

    $page->assertSee('Movilidad')
        ->assertNoJavascriptErrors();
});

// ============================================
// Tests: Detección de N+1 en páginas públicas
// ============================================

it('does not have N+1 when loading programs index', function () {
    // Los programas no tienen relaciones directas que se carguen en el index
    // Este test verifica que no hay queries duplicadas innecesarias
    Program::factory()->count(10)->create(['is_active' => true]);

    startBrowserQueryLog();
    $page = visit(route('programas.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos (activity_log, permissions, stats queries)
    // El componente ejecuta stats() que hace múltiples count() - esto es esperado
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions', 'count']);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading calls with program and academicYear', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    startBrowserQueryLog();
    $page = visit(route('convocatorias.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading news with program, author and tags', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $tags = NewsTag::factory()->count(3)->create();

    NewsPost::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ])->each(function ($news) use ($tags) {
        $news->tags()->attach($tags->random(rand(1, 3)));
    });

    startBrowserQueryLog();
    $page = visit(route('noticias.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading call show with phases and resolutions', function () {
    $data = createCallShowTestData();
    $call = $data['call'];

    startBrowserQueryLog();
    $page = visit(route('convocatorias.show', $call));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading news show with tags', function () {
    $data = createNewsShowTestData();
    $newsPost = $data['newsPost'];

    startBrowserQueryLog();
    $page = visit(route('noticias.show', $newsPost));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee($newsPost->title)
        ->assertNoJavascriptErrors();
});
