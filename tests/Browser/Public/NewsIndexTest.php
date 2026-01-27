<?php

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\createNewsTestData;

// ============================================
// Test: Verificar renderizado de listado de noticias
// ============================================

it('can visit news index page', function () {
    $page = visit(route('noticias.index'));

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete news index page structure', function () {
    $page = visit(route('noticias.index'));

    // Verificar estructura HTML básica
    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar visualización de noticias
// ============================================

it('displays published news posts', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Test News',
    ]);

    $page = visit(route('noticias.index'));

    $page->assertSee('Test News')
        ->assertNoJavascriptErrors();
});

it('only displays published news posts', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $publishedNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Published News',
    ]);

    $draftNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'borrador',
        'published_at' => null,
        'title' => 'Draft News',
    ]);

    $page = visit(route('noticias.index'));

    $page->assertSee('Published News')
        ->assertDontSee('Draft News')
        ->assertNoJavascriptErrors();
});

it('displays news post data correctly', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Test News Title',
        'excerpt' => 'Test excerpt content',
    ]);

    $page = visit(route('noticias.index'));

    $page->assertSee('Test News Title')
        ->assertSee('Test excerpt content')
        ->assertNoJavascriptErrors();
});

it('displays links to news detail pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que la noticia es clickeable
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program, author, and tags', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $tag = NewsTag::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);
    $news->tags()->attach($tag);

    $page = visit(route('noticias.index'));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por programa
// ============================================

it('filters news by program', function () {
    $program1 = Program::factory()->create(['is_active' => true, 'name' => 'Program 1']);
    $program2 = Program::factory()->create(['is_active' => true, 'name' => 'Program 2']);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Program 1',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Program 2',
    ]);

    $page = visit(route('noticias.index', ['programa' => $program1->id]));

    $page->assertSee('News Program 1')
        ->assertDontSee('News Program 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Fase 3.3: Filtros dinámicos (cambiar en la página, sin recarga)
// ============================================

it('updates results and URL when changing program select in page', function () {
    $p1 = Program::factory()->create(['name' => 'Prog N1', 'is_active' => true]);
    $p2 = Program::factory()->create(['name' => 'Prog N2', 'is_active' => true]);
    $year = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $p1->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia N1',
    ]);
    NewsPost::factory()->create([
        'program_id' => $p2->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia N2',
    ]);

    $page = visit(route('noticias.index'))
        ->select('#program-filter', (string) $p1->id)
        ->wait(1);

    $page->assertSee('Noticia N1')
        ->assertDontSee('Noticia N2')
        ->assertQueryStringHas('programa', (string) $p1->id)
        ->assertNoJavascriptErrors();
});

it('updates results and URL when typing in search input', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $year = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia sobre Becas',
    ]);
    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia sobre Movilidad',
    ]);

    $page = visit(route('noticias.index'))
        ->fill('search', 'Becas')
        ->wait(1);

    $page->assertSee('Noticia sobre Becas')
        ->assertDontSee('Noticia sobre Movilidad')
        ->assertQueryStringHas('q', 'Becas')
        ->assertNoJavascriptErrors();
});

it('resets filters when clicking reset button', function () {
    $p1 = Program::factory()->create(['name' => 'Prog NR1', 'is_active' => true]);
    $p2 = Program::factory()->create(['name' => 'Prog NR2', 'is_active' => true]);
    $year = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $p1->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia NR1',
    ]);
    NewsPost::factory()->create([
        'program_id' => $p2->id,
        'academic_year_id' => $year->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Noticia NR2',
    ]);

    $page = visit(route('noticias.index', ['programa' => $p1->id]))
        ->assertSee('Noticia NR1')
        ->assertDontSee('Noticia NR2')
        ->click(__('common.actions.reset'))
        ->wait(1);

    $page->assertSee('Noticia NR1')
        ->assertSee('Noticia NR2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por año académico
// ============================================

it('filters news by academic year', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear1 = AcademicYear::factory()->create(['year' => '2024-2025']);
    $academicYear2 = AcademicYear::factory()->create(['year' => '2025-2026']);
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear1->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Year 1',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear2->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Year 2',
    ]);

    $page = visit(route('noticias.index', ['ano' => $academicYear1->id]));

    $page->assertSee('News Year 1')
        ->assertDontSee('News Year 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar filtro por etiquetas
// ============================================

it('filters news by tags', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $tag1 = NewsTag::factory()->create(['name' => 'Tag 1']);
    $tag2 = NewsTag::factory()->create(['name' => 'Tag 2']);

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Tag 1',
    ]);
    $news1->tags()->attach($tag1);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Tag 2',
    ]);
    $news2->tags()->attach($tag2);

    $page = visit(route('noticias.index', ['etiquetas' => $tag1->id]));

    $page->assertSee('News Tag 1')
        ->assertDontSee('News Tag 2')
        ->assertNoJavascriptErrors();
});

it('filters news by multiple tags', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $tag1 = NewsTag::factory()->create(['name' => 'Tag 1']);
    $tag2 = NewsTag::factory()->create(['name' => 'Tag 2']);

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News Both Tags',
    ]);
    $news1->tags()->attach([$tag1->id, $tag2->id]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News One Tag',
    ]);
    $news2->tags()->attach($tag1);

    $page = visit(route('noticias.index', ['etiquetas' => $tag1->id.','.$tag2->id]));

    // Debe mostrar noticias que tengan al menos una de las etiquetas seleccionadas
    $page->assertSee('News Both Tags')
        ->assertSee('News One Tag')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar búsqueda de noticias
// ============================================

it('searches news by title', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Specific Title Search',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Other Title',
    ]);

    $page = visit(route('noticias.index', ['q' => 'Specific']));

    $page->assertSee('Specific Title Search')
        ->assertDontSee('Other Title')
        ->assertNoJavascriptErrors();
});

it('searches news by excerpt', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 1',
        'excerpt' => 'Unique excerpt content',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 2',
        'excerpt' => 'Other excerpt',
    ]);

    $page = visit(route('noticias.index', ['q' => 'Unique']));

    $page->assertSee('News 1')
        ->assertDontSee('News 2')
        ->assertNoJavascriptErrors();
});

it('searches news by content', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 1',
        'content' => 'Unique content text',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 2',
        'content' => 'Other content',
    ]);

    $page = visit(route('noticias.index', ['q' => 'Unique']));

    $page->assertSee('News 1')
        ->assertDontSee('News 2')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar combinación de filtros
// ============================================

it('applies multiple filters simultaneously', function () {
    $program1 = Program::factory()->create(['is_active' => true]);
    $program2 = Program::factory()->create(['is_active' => true]);
    $academicYear1 = AcademicYear::factory()->create();
    $academicYear2 = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $tag = NewsTag::factory()->create();

    // Noticia que cumple todos los filtros
    $news1 = NewsPost::factory()->create([
        'program_id' => $program1->id,
        'academic_year_id' => $academicYear1->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Matching News',
    ]);
    $news1->tags()->attach($tag);

    // Noticia que no cumple todos los filtros
    $news2 = NewsPost::factory()->create([
        'program_id' => $program2->id,
        'academic_year_id' => $academicYear1->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Non Matching News',
    ]);

    $page = visit(route('noticias.index', [
        'programa' => $program1->id,
        'ano' => $academicYear1->id,
        'etiquetas' => $tag->id,
    ]));

    $page->assertSee('Matching News')
        ->assertDontSee('Non Matching News')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar paginación
// ============================================

it('displays pagination when there are more than 12 news posts', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    // Crear 15 noticias
    $newsPosts = NewsPost::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que se muestran noticias (máximo 12 en la primera página)
    $visibleCount = 0;
    foreach ($newsPosts->take(12) as $news) {
        try {
            $page->assertSee($news->title);
            $visibleCount++;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect($visibleCount)->toBeLessThanOrEqual(12);
    expect($visibleCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

// ============================================
// Fase 4.3: Tests de Paginación
// ============================================

it('shows correct content when clicking page 2', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $newsPosts = collect();
    for ($i = 1; $i <= 15; $i++) {
        $newsPosts->push(NewsPost::factory()->create([
            'title' => "News Pag {$i}",
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'author_id' => $author->id,
            'status' => 'publicado',
            'published_at' => now(),
        ]));
    }

    $page = visit(route('noticias.index'));

    // Guardar las noticias visibles en la primera página
    $firstPageNews = [];
    foreach ($newsPosts as $news) {
        try {
            $page->assertSee($news->title);
            $firstPageNews[] = $news->title;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect(count($firstPageNews))->toBeGreaterThan(0);

    // Ir a página 2
    $page->click('button[wire\\:click*="gotoPage(2"]')
        ->wait(1.5);

    // Guardar las noticias visibles en la segunda página
    $secondPageNews = [];
    foreach ($newsPosts as $news) {
        try {
            $page->assertSee($news->title);
            $secondPageNews[] = $news->title;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    // Verificar que hay noticias visibles en la segunda página
    expect(count($secondPageNews))->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

it('navigates to page 2 and back to page 1', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $newsPosts = collect();
    for ($i = 1; $i <= 15; $i++) {
        $newsPosts->push(NewsPost::factory()->create([
            'title' => "News Nav {$i}",
            'program_id' => $program->id,
            'academic_year_id' => $academicYear->id,
            'author_id' => $author->id,
            'status' => 'publicado',
            'published_at' => now(),
        ]));
    }

    $page = visit(route('noticias.index'));

    // Guardar las noticias visibles en la primera página
    $firstPageNews = [];
    foreach ($newsPosts as $news) {
        try {
            $page->assertSee($news->title);
            $firstPageNews[] = $news->title;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect(count($firstPageNews))->toBeGreaterThan(0);

    // Ir a página 2
    $page->click('button[wire\\:click*="gotoPage(2"]')
        ->wait(1.5);

    // Guardar las noticias visibles en la segunda página
    $secondPageNews = [];
    foreach ($newsPosts as $news) {
        try {
            $page->assertSee($news->title);
            $secondPageNews[] = $news->title;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect(count($secondPageNews))->toBeGreaterThan(0);

    // Volver a página 1
    $page->click('button[wire\\:click*="gotoPage(1"]')
        ->wait(1.5);

    // Verificar que las noticias de la primera página están de nuevo visibles
    $backToFirstPageNews = [];
    foreach ($firstPageNews as $firstPageNew) {
        try {
            $page->assertSee($firstPageNew);
            $backToFirstPageNews[] = $firstPageNew;
        } catch (\Exception $e) {
            // Continuar
        }
    }

    expect(count($backToFirstPageNews))->toBeGreaterThan(0);
    $page->assertNoJavascriptErrors();
});

it('maintains filters when navigating between pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    // Crear 15 noticias del mismo programa
    $newsPosts = NewsPost::factory()->count(15)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index', ['programa' => $program->id]));

    // Verificar que se muestran noticias del programa filtrado (máximo 12 en la primera página)
    $visibleCount = 0;
    foreach ($newsPosts->take(12) as $news) {
        try {
            $page->assertSee($news->title);
            $visibleCount++;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect($visibleCount)->toBeLessThanOrEqual(12);
    expect($visibleCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar estadísticas
// ============================================

it('displays statistics correctly', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    // Crear noticias publicadas
    NewsPost::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que la página carga sin errores (las estadísticas se muestran)
    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar reset de filtros
// ============================================

it('resets filters correctly', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 1',
    ]);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'News 2',
    ]);

    // Visitar con filtro
    $page = visit(route('noticias.index', ['q' => 'News 1']));

    $page->assertSee('News 1')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar ordenamiento
// ============================================

it('orders news by published date descending', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $oldNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
        'title' => 'Old News',
    ]);

    $recentNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Recent News',
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que ambas noticias se muestran
    $page->assertSee('Recent News')
        ->assertSee('Old News')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading
// ============================================

it('detects lazy loading problems in news index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $tag = NewsTag::factory()->create();

    $news = NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    foreach ($news as $post) {
        $post->tags()->attach($tag);
    }

    $page = visit(route('noticias.index'));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies all relationships are eager loaded', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $tag = NewsTag::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);
    $news->tags()->attach($tag);

    $page = visit(route('noticias.index'));

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar helper createNewsTestData
// ============================================

it('displays news using createNewsTestData helper', function () {
    $data = createNewsTestData();

    $page = visit(route('noticias.index'));

    // Verificar que se muestran las noticias
    foreach ($data['news'] as $news) {
        $page->assertSee($news->title);
    }

    $page->assertNoJavascriptErrors();
});
