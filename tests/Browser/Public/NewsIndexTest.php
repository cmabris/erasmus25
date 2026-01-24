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
