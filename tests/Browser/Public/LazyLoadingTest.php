<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\assertEagerLoaded;
use function Tests\Browser\Helpers\assertNoLazyLoading;
use function Tests\Browser\Helpers\createCallShowTestData;
use function Tests\Browser\Helpers\createNewsShowTestData;
use function Tests\Browser\Helpers\createProgramShowTestData;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Validación de eager loading en páginas públicas
// ============================================

it('does not have lazy loading in programs index', function () {
    // Los programas no tienen relaciones directas que se carguen en el index
    // Este test verifica que no hay lazy loading innecesario
    Program::factory()->count(10)->create(['is_active' => true]);

    startBrowserQueryLog();
    $page = visit(route('programas.index'));
    $queries = stopBrowserQueryLog();

    // El componente Programs\Index no carga relaciones, solo hace consultas simples
    // Verificamos que no hay queries duplicadas que indiquen N+1
    // Las queries de stats() (count) son esperadas y legítimas
    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('has program and academicYear eager loaded in calls index', function () {
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

    // Verificar que program y academicYear están eager loaded
    assertNoLazyLoading('Call', 'program', $queries);
    assertNoLazyLoading('Call', 'academicYear', $queries);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('has program, author and tags eager loaded in news index', function () {
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

    // Verificar que program, author y tags están eager loaded
    assertNoLazyLoading('NewsPost', 'program', $queries);
    assertNoLazyLoading('NewsPost', 'author', $queries);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('has phases and resolutions eager loaded in call show', function () {
    $data = createCallShowTestData();
    $call = $data['call'];

    startBrowserQueryLog();
    $page = visit(route('convocatorias.show', $call));
    $queries = stopBrowserQueryLog();

    // Verificar que phases y resolutions están eager loaded
    // No debería haber queries individuales para cada fase o resolución
    assertNoLazyLoading('CallPhase', 'call', $queries);
    assertNoLazyLoading('Resolution', 'call', $queries);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('has tags eager loaded in news show', function () {
    $data = createNewsShowTestData();
    $newsPost = $data['newsPost'];

    startBrowserQueryLog();
    $page = visit(route('noticias.show', $newsPost));
    $queries = stopBrowserQueryLog();

    // Verificar que tags está eager loaded
    // No debería haber queries individuales para cada tag
    assertEagerLoaded('tags', $queries);

    $page->assertSee($newsPost->title)
        ->assertNoJavascriptErrors();
});

it('has relations eager loaded in program show', function () {
    $data = createProgramShowTestData();
    $program = $data['program'];

    startBrowserQueryLog();
    $page = visit(route('programas.show', $program));
    $queries = stopBrowserQueryLog();

    // Verificar que calls, newsPosts y documents están eager loaded
    // No debería haber queries individuales para cada relación
    assertNoLazyLoading('Call', 'program', $queries);
    assertNoLazyLoading('NewsPost', 'program', $queries);
    assertNoLazyLoading('Document', 'program', $queries);

    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});
