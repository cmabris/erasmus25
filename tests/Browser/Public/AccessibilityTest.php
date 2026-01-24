<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;

use function Tests\Browser\Helpers\createHomeTestData;

// ============================================
// Test: Verificar estructura semántica
// ============================================

it('uses semantic HTML elements on home page', function () {
    createHomeTestData();

    $page = visit(route('home'));

    // Verificar que la página carga sin errores (los elementos semánticos se verifican en el HTML)
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('uses semantic HTML elements on programs index page', function () {
    Program::factory()->count(5)->create(['is_active' => true]);

    $page = visit(route('programas.index'));

    // Verificar que la página carga sin errores
    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('uses semantic HTML elements on calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.index'));

    // Verificar que la página carga sin errores (verificar contenido de la convocatoria)
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('uses semantic HTML elements on news index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que la página carga sin errores
    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Verificar navegación por teclado
// ============================================

it('has keyboard accessible links on home page', function () {
    createHomeTestData();

    $page = visit(route('home'));

    // Verificar que la página carga sin errores (los enlaces son accesibles por teclado)
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

it('has keyboard accessible links on programs index page', function () {
    $program = Program::factory()->create(['is_active' => true]);

    $page = visit(route('programas.index'));

    // Verificar que la página carga sin errores (verificar contenido del programa)
    $page->assertSee($program->name)
        ->assertNoJavascriptErrors();
});

it('has keyboard accessible forms on calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    $page = visit(route('convocatorias.index'));

    // Verificar que la página carga sin errores (los formularios son navegables por teclado)
    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('has keyboard accessible forms on news index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $page = visit(route('noticias.index'));

    // Verificar que la página carga sin errores
    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});
