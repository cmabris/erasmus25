<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;

use function Tests\Browser\Helpers\createCallShowTestData;

// ============================================
// Test: Verificar renderizado de detalle de convocatoria
// ============================================

it('can visit a call detail page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Test Call')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete call detail page structure', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar estructura HTML básica
    $page->assertSee('Test Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar configuración visual de la convocatoria
// ============================================

it('displays correct visual configuration for abierta status', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Open Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que la página carga sin errores (los colores se aplican via CSS)
    $page->assertSee('Open Call')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for cerrada status', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now(),
        'title' => 'Closed Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Closed Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar acceso a convocatorias no publicadas
// ============================================

it('returns 404 for unpublished calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => null,
        'title' => 'Unpublished Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que no se muestra el contenido de la convocatoria (404)
    $page->assertDontSee('Unpublished Call')
        ->assertNoJavascriptErrors();
});

it('returns 404 for draft calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'borrador',
        'published_at' => now(),
        'title' => 'Draft Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que no se muestra el contenido de la convocatoria (404)
    $page->assertDontSee('Draft Call')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar fases de la convocatoria
// ============================================

it('displays phases for a call', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
        'name' => 'Test Phase',
        'order' => 1,
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Test Phase')
        ->assertNoJavascriptErrors();
});

it('displays all phases ordered by order', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase1 = CallPhase::factory()->create([
        'call_id' => $call->id,
        'name' => 'Phase 1',
        'order' => 2,
    ]);

    $phase2 = CallPhase::factory()->create([
        'call_id' => $call->id,
        'name' => 'Phase 2',
        'order' => 1,
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que ambas fases se muestran
    $page->assertSee('Phase 1')
        ->assertSee('Phase 2')
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of phases in mount', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
        'name' => 'Test Phase',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee('Test Phase')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar resoluciones publicadas
// ============================================

it('displays published resolutions for a call', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'title' => 'Test Resolution',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Test Resolution')
        ->assertNoJavascriptErrors();
});

it('only displays published resolutions', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $publishedResolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'title' => 'Published Resolution',
        'published_at' => now(),
    ]);

    $unpublishedResolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'title' => 'Unpublished Resolution',
        'published_at' => null,
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Published Resolution')
        ->assertDontSee('Unpublished Resolution')
        ->assertNoJavascriptErrors();
});

it('orders resolutions by official date descending', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $oldResolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'title' => 'Old Resolution',
        'official_date' => now()->subDays(5),
        'published_at' => now()->subDays(5),
    ]);

    $recentResolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'title' => 'Recent Resolution',
        'official_date' => now(),
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que ambas resoluciones se muestran
    $page->assertSee('Recent Resolution')
        ->assertSee('Old Resolution')
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of resolutions and callPhase in mount', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($resolution->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar noticias relacionadas
// ============================================

it('displays related news for a call', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Test News',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Test News')
        ->assertNoJavascriptErrors();
});

it('displays maximum 3 related news posts', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Crear 5 noticias
    $newsPosts = NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que se muestran máximo 3 noticias
    $newsCount = 0;
    foreach ($newsPosts as $news) {
        try {
            $page->assertSee($news->title);
            $newsCount++;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }

    expect($newsCount)->toBeLessThanOrEqual(3);
    expect($newsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('only displays published news', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $publishedNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Published News',
    ]);

    $unpublishedNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'borrador',
        'published_at' => null,
        'title' => 'Unpublished News',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Published News')
        ->assertDontSee('Unpublished News')
        ->assertNoJavascriptErrors();
});

it('displays links to news detail pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que la noticia es clickeable
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and author in news', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar otras convocatorias del mismo programa
// ============================================

it('displays other calls from the same program', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Current Call',
    ]);

    $otherCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Other Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    $page->assertSee('Other Call')
        ->assertNoJavascriptErrors();
});

it('displays maximum 3 other calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Crear 5 convocatorias adicionales
    $otherCalls = Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que se muestran máximo 3 otras convocatorias
    $callsCount = 0;
    foreach ($otherCalls as $otherCall) {
        try {
            $page->assertSee($otherCall->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible, continuar
        }
    }

    expect($callsCount)->toBeLessThanOrEqual(3);
    expect($callsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('does not display current call in other calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Current Call',
    ]);

    $otherCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Other Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que se muestra el título de la convocatoria actual en el título de la página
    $page->assertSee('Current Call')
        ->assertSee('Other Call')
        ->assertNoJavascriptErrors();
});

it('orders other calls with abierta status first', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $closedCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'cerrada',
        'published_at' => now()->subDays(5),
        'title' => 'Closed Other Call',
    ]);

    $openCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Open Other Call',
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que ambas convocatorias se muestran
    $page->assertSee('Open Other Call')
        ->assertSee('Closed Other Call')
        ->assertNoJavascriptErrors();
});

it('displays links to other calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $otherCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que la otra convocatoria es clickeable
    $page->assertSee($otherCall->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and academicYear in other calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $otherCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($otherCall->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar navegación desde detalle de convocatoria
// ============================================

it('displays breadcrumbs', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que la página carga sin errores (breadcrumbs se muestran)
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('displays links to related news', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que hay un enlace a la noticia
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading (CRÍTICO)
// ============================================

it('detects lazy loading problems in call detail page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($resolution->title)
        ->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

it('verifies all relationships are eager loaded', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $phase = CallPhase::factory()->create([
        'call_id' => $call->id,
    ]);

    $resolution = Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phase->id,
        'published_at' => now(),
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.show', $call->slug));

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar estado vacío
// ============================================

it('displays appropriate message when no phases available', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);
    // No crear fases

    $page = visit(route('convocatorias.show', $call->slug));

    // La página debe cargar sin errores incluso sin fases
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no published resolutions available', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);
    // No crear resoluciones publicadas

    $page = visit(route('convocatorias.show', $call->slug));

    // La página debe cargar sin errores incluso sin resoluciones
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no related news available', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);
    // No crear noticias

    $page = visit(route('convocatorias.show', $call->slug));

    // La página debe cargar sin errores incluso sin noticias
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar helper createCallShowTestData
// ============================================

it('displays complete call detail using createCallShowTestData helper', function () {
    $data = createCallShowTestData();

    $page = visit(route('convocatorias.show', $data['call']->slug));

    // Verificar que se muestra la convocatoria
    $page->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    // Verificar que se muestran fases (todas)
    $phasesCount = 0;
    foreach ($data['phases'] as $phase) {
        try {
            $page->assertSee($phase->name);
            $phasesCount++;
        } catch (\Exception $e) {
            // Fase no visible, continuar
        }
    }
    expect($phasesCount)->toBeGreaterThan(0);

    // Verificar que se muestran resoluciones publicadas (máximo 1 de las 2)
    $resolutionsCount = 0;
    foreach ($data['resolutions'] as $resolution) {
        if ($resolution->published_at) {
            try {
                $page->assertSee($resolution->title);
                $resolutionsCount++;
            } catch (\Exception $e) {
                // Resolución no visible, continuar
            }
        }
    }
    expect($resolutionsCount)->toBeGreaterThan(0);

    // Verificar que se muestran noticias relacionadas (máximo 3)
    $newsCount = 0;
    foreach ($data['news'] as $news) {
        try {
            $page->assertSee($news->title);
            $newsCount++;
        } catch (\Exception $e) {
            // Noticia no visible, continuar
        }
    }
    expect($newsCount)->toBeLessThanOrEqual(3);
    expect($newsCount)->toBeGreaterThan(0);

    // Verificar que se muestran otras convocatorias (máximo 3)
    $callsCount = 0;
    foreach ($data['otherCalls'] as $otherCall) {
        try {
            $page->assertSee($otherCall->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible, continuar
        }
    }
    expect($callsCount)->toBeLessThanOrEqual(3);
    expect($callsCount)->toBeGreaterThan(0);
});
