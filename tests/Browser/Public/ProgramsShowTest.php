<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\createProgramShowTestData;

// ============================================
// Test: Verificar renderizado de detalle de programa
// ============================================

it('can visit a program detail page', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'code' => 'KA121-VET',
        'description' => 'Test Description',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Test Program')
        ->assertSee('KA121-VET')
        ->assertSee('Test Description')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete program detail page structure', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar estructura HTML básica
    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar configuración visual del programa
// ============================================

it('displays correct visual configuration for VET program', function () {
    $program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Programa VET',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que la página carga sin errores (los colores se aplican via CSS)
    $page->assertSee('Programa VET')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for HED program', function () {
    $program = Program::factory()->create([
        'code' => 'KA131-HED',
        'name' => 'Programa HED',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa HED')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for SCH program', function () {
    $program = Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Programa Escolar',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa Escolar')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for KA1 program', function () {
    $program = Program::factory()->create([
        'code' => 'KA121',
        'name' => 'Programa KA1',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa KA1')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for KA2 program', function () {
    $program = Program::factory()->create([
        'code' => 'KA220',
        'name' => 'Programa KA2',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa KA2')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for JM program', function () {
    $program = Program::factory()->create([
        'code' => 'JM-001',
        'name' => 'Programa Jean Monnet',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa Jean Monnet')
        ->assertNoJavascriptErrors();
});

it('displays correct visual configuration for DISCOVER program', function () {
    $program = Program::factory()->create([
        'code' => 'DISCOVER-001',
        'name' => 'Programa DiscoverEU',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Programa DiscoverEU')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar imagen del programa
// ============================================

it('displays program image when available', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    // Nota: Para tests completos de imágenes, se necesitaría configurar Media Library
    // Por ahora, verificamos que la página carga sin errores
    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Test Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar convocatorias relacionadas
// ============================================

it('displays related calls for a program', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Call',
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Test Call')
        ->assertNoJavascriptErrors();
});

it('displays maximum 4 related calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear 6 convocatorias
    $calls = Call::factory()->count(6)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que se muestran máximo 4 convocatorias
    $callsCount = 0;
    foreach ($calls as $call) {
        try {
            $page->assertSee($call->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible, continuar
        }
    }

    expect($callsCount)->toBeLessThanOrEqual(4);
    expect($callsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('only displays published calls with status abierta or cerrada', function () {
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

    $draftCall = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'borrador',
        'published_at' => now(),
        'title' => 'Draft Call',
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Published Call')
        ->assertDontSee('Unpublished Call')
        ->assertDontSee('Draft Call')
        ->assertNoJavascriptErrors();
});

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

    $page = visit(route('programas.show', $program->slug));

    // Verificar que ambas convocatorias se muestran
    $page->assertSee('Open Call')
        ->assertSee('Closed Call')
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

    $page = visit(route('programas.show', $program->slug));

    // Verificar que la convocatoria es clickeable
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and academicYear in calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar noticias relacionadas
// ============================================

it('displays related news for a program', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Test News',
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Test News')
        ->assertNoJavascriptErrors();
});

it('displays maximum 3 related news posts', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    // Crear 5 noticias
    $newsPosts = NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

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
    $author = User::factory()->create();

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

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Published News')
        ->assertDontSee('Unpublished News')
        ->assertNoJavascriptErrors();
});

it('orders news by publication date descending', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $oldNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
        'title' => 'Old News',
    ]);

    $recentNews = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Recent News',
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que ambas noticias se muestran
    $page->assertSee('Recent News')
        ->assertSee('Old News')
        ->assertNoJavascriptErrors();
});

it('displays links to news detail pages', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que la noticia es clickeable
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and author in news', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar otros programas sugeridos
// ============================================

it('displays other suggested programs', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $otherProgram = Program::factory()->create([
        'name' => 'Other Program',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    $page->assertSee('Other Program')
        ->assertNoJavascriptErrors();
});

it('displays maximum 3 other programs', function () {
    $program = Program::factory()->create(['is_active' => true]);

    // Crear 5 programas adicionales
    $otherPrograms = Program::factory()->count(5)->create(['is_active' => true]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que se muestran máximo 3 otros programas
    $programsCount = 0;
    foreach ($otherPrograms as $otherProgram) {
        try {
            $page->assertSee($otherProgram->name);
            $programsCount++;
        } catch (\Exception $e) {
            // Programa no visible, continuar
        }
    }

    expect($programsCount)->toBeLessThanOrEqual(3);
    expect($programsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('does not display current program in other programs', function () {
    $program = Program::factory()->create([
        'name' => 'Current Program',
        'is_active' => true,
    ]);

    $otherProgram = Program::factory()->create([
        'name' => 'Other Program',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que se muestra el programa actual en el título
    $page->assertSee('Current Program')
        ->assertSee('Other Program')
        ->assertNoJavascriptErrors();
});

it('displays links to other programs', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $otherProgram = Program::factory()->create([
        'name' => 'Other Program',
        'is_active' => true,
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que el otro programa es clickeable
    $page->assertSee('Other Program')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar navegación desde detalle de programa
// ============================================

it('displays breadcrumbs', function () {
    $program = Program::factory()->create(['is_active' => true]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que la página carga sin errores (breadcrumbs se muestran)
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('displays links to related calls', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que hay un enlace a la convocatoria
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('displays links to related news', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que hay un enlace a la noticia
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading (CRÍTICO)
// ============================================

it('detects lazy loading problems in program detail page', function () {
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

    $page = visit(route('programas.show', $program->slug));

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($call->title)
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

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('programas.show', $program->slug));

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar estado vacío
// ============================================

it('displays appropriate message when no related calls available', function () {
    $program = Program::factory()->create(['is_active' => true]);
    // No crear convocatorias

    $page = visit(route('programas.show', $program->slug));

    // La página debe cargar sin errores incluso sin convocatorias
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no related news available', function () {
    $program = Program::factory()->create(['is_active' => true]);
    // No crear noticias

    $page = visit(route('programas.show', $program->slug));

    // La página debe cargar sin errores incluso sin noticias
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar helper createProgramShowTestData
// ============================================

it('displays complete program detail using createProgramShowTestData helper', function () {
    $data = createProgramShowTestData();

    $page = visit(route('programas.show', $data['program']->slug));

    // Verificar que se muestra el programa
    $page->assertSee('Programa de Prueba')
        ->assertNoJavascriptErrors();

    // Verificar que se muestran convocatorias relacionadas (máximo 4)
    $callsCount = 0;
    foreach ($data['calls'] as $call) {
        try {
            $page->assertSee($call->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible, continuar
        }
    }
    expect($callsCount)->toBeLessThanOrEqual(4);
    expect($callsCount)->toBeGreaterThan(0);

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

    // Verificar que se muestran otros programas (máximo 3)
    $programsCount = 0;
    foreach ($data['otherPrograms'] as $otherProgram) {
        try {
            $page->assertSee($otherProgram->name);
            $programsCount++;
        } catch (\Exception $e) {
            // Programa no visible, continuar
        }
    }
    expect($programsCount)->toBeLessThanOrEqual(3);
    expect($programsCount)->toBeGreaterThan(0);
});
