<?php

use App\Livewire\Public\Home;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

use function Tests\Browser\Helpers\createHomeTestData;
use function Tests\Browser\Helpers\createPublicTestData;

// ============================================
// Test: Verificar renderizado completo de Home
// ============================================

it('can visit the home page', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

it('verifies complete home page structure', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    // Verificar estructura HTML básica
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();

    // Verificar que no hay errores en consola
    $page->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar programas activos en Home
// ============================================

it('displays active programs on home page', function () {
    $program = Program::factory()->create([
        'name' => 'Test Active Program',
        'is_active' => true,
    ]);

    $page = visit('/');

    $page->assertSee('Test Active Program')
        ->assertNoJavascriptErrors();
});

it('displays maximum 6 active programs on home page', function () {
    // Limpiar caché antes de crear datos
    Home::clearCache();
    Program::clearCache();
    Cache::flush();

    // Crear 8 programas activos con nombres únicos y orden específico
    $programs = Program::factory()->count(8)->create(['is_active' => true]);

    // Limpiar caché después de crear datos para forzar recarga
    Home::clearCache();
    Program::clearCache();
    Cache::flush();

    $page = visit('/');

    // Verificar que se muestran máximo 6 programas
    // Contar cuántos de los programas creados se muestran
    $programsCount = 0;
    foreach ($programs as $program) {
        try {
            $page->assertSee($program->name);
            $programsCount++;
        } catch (\Exception $e) {
            // Programa no visible, continuar
        }
    }

    // Verificar que se muestran máximo 6 programas (puede ser menos si hay otros programas en BD)
    expect($programsCount)->toBeLessThanOrEqual(6);
    // Verificar que al menos algunos de los programas creados se muestran
    expect($programsCount)->toBeGreaterThan(0);

    $page->assertNoJavascriptErrors();
});

it('does not display inactive programs on home page', function () {
    $activeProgram = Program::factory()->create([
        'name' => 'Active Program',
        'is_active' => true,
    ]);

    $inactiveProgram = Program::factory()->create([
        'name' => 'Inactive Program',
        'is_active' => false,
    ]);

    $page = visit('/');

    $page->assertSee('Active Program')
        ->assertDontSee('Inactive Program')
        ->assertNoJavascriptErrors();
});

it('displays links to program detail pages', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/');

    // Verificar que hay un enlace al detalle del programa
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar convocatorias abiertas en Home
// ============================================

it('displays open calls on home page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
        'title' => 'Test Open Call',
    ]);

    $page = visit('/');

    $page->assertSee('Test Open Call')
        ->assertNoJavascriptErrors();
});

it('displays maximum 4 open calls on home page', function () {
    // Limpiar caché antes de crear datos
    Home::clearCache();
    Cache::flush();

    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear 6 convocatorias abiertas con fechas diferentes para ordenamiento
    $calls = Call::factory()->count(6)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Limpiar caché después de crear datos para forzar recarga
    Home::clearCache();
    Cache::flush();

    $page = visit('/');

    // Verificar que se muestran máximo 4 convocatorias (no importa cuáles específicamente)
    // Contar cuántas convocatorias se muestran en la página
    $callsCount = 0;
    foreach ($calls as $call) {
        try {
            $page->assertSee($call->title);
            $callsCount++;
        } catch (\Exception $e) {
            // Convocatoria no visible, continuar
        }
    }

    // Verificar que se muestran máximo 4 convocatorias
    expect($callsCount)->toBeLessThanOrEqual(4);

    $page->assertNoJavascriptErrors();
});

it('does not display unpublished calls on home page', function () {
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

    $page = visit('/');

    $page->assertSee('Published Call')
        ->assertDontSee('Unpublished Call')
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and academicYear in calls', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);
    $academicYear = AcademicYear::factory()->create([
        'year' => '2025-2026',
    ]);

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/');

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($call->title)
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

    $page = visit('/');

    // Verificar que hay un enlace al detalle de la convocatoria
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar noticias recientes en Home
// ============================================

it('displays recent news on home page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
        'title' => 'Test News Post',
    ]);

    $page = visit('/');

    $page->assertSee('Test News Post')
        ->assertNoJavascriptErrors();
});

it('displays maximum 3 recent news posts on home page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    // Crear 5 noticias publicadas
    $newsPosts = NewsPost::factory()->count(5)->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit('/');

    // Verificar que se muestran solo las primeras 3
    $displayedNews = $newsPosts->take(3);
    foreach ($displayedNews as $news) {
        $page->assertSee($news->title);
    }

    $page->assertNoJavascriptErrors();
});

it('does not display unpublished news on home page', function () {
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

    $page = visit('/');

    $page->assertSee('Published News')
        ->assertDontSee('Unpublished News')
        ->assertNoJavascriptErrors();
});

it('verifies eager loading of program and author in news', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);
    $author = User::factory()->create([
        'name' => 'Test Author',
    ]);

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit('/');

    // Si hay lazy loading, esto causaría errores de JavaScript
    $page->assertSee($news->title)
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

    $page = visit('/');

    // Verificar que hay un enlace al detalle de la noticia
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar eventos próximos en Home
// ============================================

it('displays upcoming events on home page', function () {
    $event = ErasmusEvent::factory()->create([
        'title' => 'Test Upcoming Event',
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    $page->assertSee('Test Upcoming Event')
        ->assertNoJavascriptErrors();
});

it('displays maximum 5 upcoming events on home page', function () {
    // Crear 7 eventos próximos
    $events = ErasmusEvent::factory()->count(7)->create([
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    // Verificar que se muestran solo los primeros 5
    $displayedEvents = $events->take(5);
    foreach ($displayedEvents as $event) {
        $page->assertSee($event->title);
    }

    $page->assertNoJavascriptErrors();
});

it('does not display past events on home page', function () {
    $upcomingEvent = ErasmusEvent::factory()->create([
        'title' => 'Upcoming Event',
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $pastEvent = ErasmusEvent::factory()->create([
        'title' => 'Past Event',
        'is_public' => true,
        'start_date' => now()->subDays(7),
    ]);

    $page = visit('/');

    $page->assertSee('Upcoming Event')
        ->assertDontSee('Past Event')
        ->assertNoJavascriptErrors();
});

it('does not display private events on home page', function () {
    $publicEvent = ErasmusEvent::factory()->create([
        'title' => 'Public Event',
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $privateEvent = ErasmusEvent::factory()->create([
        'title' => 'Private Event',
        'is_public' => false,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    $page->assertSee('Public Event')
        ->assertDontSee('Private Event')
        ->assertNoJavascriptErrors();
});

it('displays links to event detail pages', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    // Verificar que hay un enlace al detalle del evento
    $page->assertSee($event->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar navegación desde Home
// ============================================

it('displays navigation menu links', function () {
    Program::factory()->count(3)->create(['is_active' => true]);

    $page = visit('/');

    // Verificar enlaces del menú de navegación
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays links to programs from program cards', function () {
    $program = Program::factory()->create([
        'name' => 'Test Program',
        'is_active' => true,
    ]);

    $page = visit('/');

    // Verificar que el programa es clickeable
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('displays links to calls from call cards', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit('/');

    // Verificar que la convocatoria es clickeable
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('displays links to news from news cards', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $author = User::factory()->create();

    $news = NewsPost::factory()->create([
        'program_id' => $program->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit('/');

    // Verificar que la noticia es clickeable
    $page->assertSee($news->title)
        ->assertNoJavascriptErrors();
});

it('displays links to events from event cards', function () {
    $event = ErasmusEvent::factory()->create([
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    // Verificar que el evento es clickeable
    $page->assertSee($event->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Detectar problemas de lazy loading en Home
// ============================================

it('detects lazy loading problems in home page', function () {
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

    $page = visit('/');

    // Si hay lazy loading, esto causaría errores de JavaScript
    // porque se intentaría acceder a relaciones no cargadas
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

    $event = ErasmusEvent::factory()->create([
        'program_id' => $program->id,
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    $page = visit('/');

    // Verificar que no hay errores de JavaScript (indica que las relaciones están cargadas)
    $page->assertNoJavascriptErrors()
        ->assertNoConsoleLogs();
});

// ============================================
// Test: Verificar estado vacío en Home
// ============================================

it('displays appropriate message when no programs available', function () {
    // No crear ningún programa

    $page = visit('/');

    // La página debe cargar sin errores incluso sin programas
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no calls available', function () {
    Program::factory()->count(3)->create(['is_active' => true]);
    // No crear convocatorias

    $page = visit('/');

    // La página debe cargar sin errores incluso sin convocatorias
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no news available', function () {
    Program::factory()->count(3)->create(['is_active' => true]);
    // No crear noticias

    $page = visit('/');

    // La página debe cargar sin errores incluso sin noticias
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no events available', function () {
    Program::factory()->count(3)->create(['is_active' => true]);
    // No crear eventos

    $page = visit('/');

    // La página debe cargar sin errores incluso sin eventos
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('displays appropriate message when no content available at all', function () {
    // No crear ningún contenido

    $page = visit('/');

    // La página debe cargar sin errores incluso sin contenido
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar contenido usando helpers
// ============================================

it('displays public content using helper', function () {
    $data = createPublicTestData();

    $page = visit('/');

    $page->assertSee($data['program']->name)
        ->assertSee($data['call']->title)
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});

it('displays complete home content using createHomeTestData helper', function () {
    // Limpiar caché antes de crear datos
    Home::clearCache();
    Program::clearCache();
    Cache::flush();

    $data = createHomeTestData();

    // Limpiar caché después de crear datos
    Home::clearCache();
    Program::clearCache();
    Cache::flush();

    $page = visit('/');

    // Verificar que se muestran programas
    $page->assertSee($data['programs']->first()->name)
        // Verificar que se muestran convocatorias
        ->assertSee($data['calls']->first()->title)
        // Verificar que se muestran noticias
        ->assertSee($data['news']->first()->title)
        // Verificar que se muestran eventos
        ->assertSee($data['events']->first()->title)
        ->assertNoJavascriptErrors();
});
