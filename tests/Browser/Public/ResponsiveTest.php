<?php

use function Tests\Browser\Helpers\assertNoHorizontalScroll;
use function Tests\Browser\Helpers\createCallShowTestData;
use function Tests\Browser\Helpers\createCallsTestData;
use function Tests\Browser\Helpers\createGlobalSearchTestData;
use function Tests\Browser\Helpers\createHomeTestData;
use function Tests\Browser\Helpers\createNewsShowTestData;
use function Tests\Browser\Helpers\createNewsTestData;
use function Tests\Browser\Helpers\createProgramShowTestData;
use function Tests\Browser\Helpers\createProgramsTestData;

// ============================================
// Fase 1.1: Tests de Home responsive
// ============================================

it('home page looks good on mobile', function () {
    $data = createHomeTestData();

    $page = visit(route('home'))
        ->on()->mobile();

    // Verificar que el menú móvil está visible (botón hamburguesa)
    $page->assertSee(__('common.nav.open_menu'))
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);

    // Verificar que los programas se muestran (el layout específico se verifica visualmente)
    $page->assertSee($data['programs']->first()->name);

    // Verificar que las convocatorias se muestran
    $page->assertSee($data['calls']->first()->title);

    // Verificar que las noticias se muestran
    $page->assertSee($data['news']->first()->title);

    $page->assertNoJavascriptErrors();
});

it('home page looks good on tablet', function () {
    $data = createHomeTestData();

    $page = visit(route('home'))
        ->resize(768, 1024);

    // Verificar que la página carga correctamente
    $page->assertSee($data['programs']->first()->name)
        ->assertSee($data['calls']->first()->title)
        ->assertSee($data['news']->first()->title)
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);
});

it('home page looks good on desktop', function () {
    $data = createHomeTestData();

    $page = visit(route('home'))
        ->on()->desktop();

    // Verificar que el menú desktop está visible (no hamburguesa en desktop)
    // El menú móvil no debería estar visible en desktop
    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);

    // Verificar que los programas se muestran
    $page->assertSee($data['programs']->first()->name);

    // Verificar que las convocatorias se muestran
    $page->assertSee($data['calls']->first()->title);

    // Verificar que las noticias se muestran
    $page->assertSee($data['news']->first()->title);

    $page->assertNoJavascriptErrors();
});

// ============================================
// Fase 1.2: Tests de Programs Index responsive
// ============================================

it('programs index looks good on mobile', function () {
    $data = createProgramsTestData();

    $page = visit(route('programas.index'))
        ->on()->mobile();

    // Verificar que la página carga correctamente
    $page->assertSee($data['programs']->first()->name)
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);
});

it('programs index looks good on tablet', function () {
    $data = createProgramsTestData();

    $page = visit(route('programas.index'))
        ->resize(768, 1024);

    $page->assertSee($data['programs']->first()->name)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('programs index looks good on desktop', function () {
    $data = createProgramsTestData();

    $page = visit(route('programas.index'))
        ->on()->desktop();

    $page->assertSee($data['programs']->first()->name)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.3: Tests de Programs Show responsive
// ============================================

it('programs show looks good on mobile', function () {
    $data = createProgramShowTestData();

    $page = visit(route('programas.show', $data['program']))
        ->on()->mobile();

    // Verificar que el contenido principal es legible
    $page->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('programs show looks good on tablet', function () {
    $data = createProgramShowTestData();

    $page = visit(route('programas.show', $data['program']))
        ->resize(768, 1024);

    $page->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('programs show looks good on desktop', function () {
    $data = createProgramShowTestData();

    $page = visit(route('programas.show', $data['program']))
        ->on()->desktop();

    $page->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.4: Tests de Calls Index responsive
// ============================================

it('calls index looks good on mobile', function () {
    $data = createCallsTestData();

    $page = visit(route('convocatorias.index'))
        ->on()->mobile();

    $page->assertSee($data['calls']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('calls index looks good on tablet', function () {
    $data = createCallsTestData();

    $page = visit(route('convocatorias.index'))
        ->resize(768, 1024);

    $page->assertSee($data['calls']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('calls index looks good on desktop', function () {
    $data = createCallsTestData();

    $page = visit(route('convocatorias.index'))
        ->on()->desktop();

    $page->assertSee($data['calls']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.5: Tests de Calls Show responsive
// ============================================

it('calls show looks good on mobile', function () {
    $data = createCallShowTestData();

    $page = visit(route('convocatorias.show', $data['call']))
        ->on()->mobile();

    $page->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('calls show looks good on tablet', function () {
    $data = createCallShowTestData();

    $page = visit(route('convocatorias.show', $data['call']))
        ->resize(768, 1024);

    $page->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('calls show looks good on desktop', function () {
    $data = createCallShowTestData();

    $page = visit(route('convocatorias.show', $data['call']))
        ->on()->desktop();

    $page->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.6: Tests de News Index responsive
// ============================================

it('news index looks good on mobile', function () {
    $data = createNewsTestData();

    $page = visit(route('noticias.index'))
        ->on()->mobile();

    $page->assertSee($data['news']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('news index looks good on tablet', function () {
    $data = createNewsTestData();

    $page = visit(route('noticias.index'))
        ->resize(768, 1024);

    $page->assertSee($data['news']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('news index looks good on desktop', function () {
    $data = createNewsTestData();

    $page = visit(route('noticias.index'))
        ->on()->desktop();

    $page->assertSee($data['news']->first()->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.7: Tests de News Show responsive
// ============================================

it('news show looks good on mobile', function () {
    $data = createNewsShowTestData();

    $page = visit(route('noticias.show', $data['newsPost']))
        ->on()->mobile();

    // Verificar que el contenido de la noticia es legible
    $page->assertSee($data['newsPost']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('news show looks good on tablet', function () {
    $data = createNewsShowTestData();

    $page = visit(route('noticias.show', $data['newsPost']))
        ->resize(768, 1024);

    $page->assertSee($data['newsPost']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('news show looks good on desktop', function () {
    $data = createNewsShowTestData();

    $page = visit(route('noticias.show', $data['newsPost']))
        ->on()->desktop();

    $page->assertSee($data['newsPost']->title)
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 1.9: Tests de Global Search responsive
// ============================================

it('global search looks good on mobile', function () {
    createGlobalSearchTestData();

    $page = visit(route('search'))
        ->on()->mobile();

    // Verificar que el input de búsqueda es accesible
    $page->assertSee(__('common.search.global_title'))
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('global search looks good on tablet', function () {
    createGlobalSearchTestData();

    $page = visit(route('search'))
        ->resize(768, 1024);

    $page->assertSee(__('common.search.global_title'))
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('global search looks good on desktop', function () {
    createGlobalSearchTestData();

    $page = visit(route('search'))
        ->on()->desktop();

    $page->assertSee(__('common.search.global_title'))
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});
