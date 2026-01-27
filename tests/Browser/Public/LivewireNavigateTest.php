<?php

use Illuminate\Support\Facades\App;

use function Tests\Browser\Helpers\createPublicTestData;

beforeEach(function () {
    App::setLocale('es');
});

// ============================================
// Test: Navegación desde Home a Programas sin full reload
// ============================================

it('navigates from home to programs without full reload using wire:navigate', function () {
    $data = createPublicTestData();

    $page = visit('/')
        ->assertSee('Erasmus+')
        ->click(__('common.nav.programs'))
        ->wait(1);

    $page->assertPathIs('/programas')
        ->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Navegación desde Programas a Convocatorias
// ============================================

it('navigates from programs to calls using wire:navigate', function () {
    $data = createPublicTestData();

    $page = visit('/programas')
        ->assertSee($data['program']->name)
        ->click(__('common.nav.calls'))
        ->wait(1);

    $page->assertPathIs('/convocatorias')
        ->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Navegación desde Convocatorias a Noticias
// ============================================

it('navigates from calls to news using wire:navigate', function () {
    $data = createPublicTestData();

    $page = visit('/convocatorias')
        ->assertSee($data['call']->title)
        ->click(__('common.nav.news'))
        ->wait(1);

    $page->assertPathIs('/noticias')
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Navegación desde Noticias a Búsqueda (Buscar)
// ============================================

it('navigates from news to search using wire:navigate', function () {
    $data = createPublicTestData();

    $page = visit(route('noticias.index'))
        ->assertSee($data['news']->title)
        ->click(__('common.search.global_title'))
        ->wait(1);

    $page->assertPathIs('/buscar')
        ->assertSee(__('common.search.global_title'))
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Navegación desde un listado a un detalle (wire:navigate)
// ============================================

it('navigates from programs list to program detail using wire:navigate', function () {
    $data = createPublicTestData();

    $page = visit('/programas')
        ->assertSee($data['program']->name)
        ->click($data['program']->name)
        ->wait(1);

    $page->assertPathBeginsWith('/programas/')
        ->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: La URL se actualiza correctamente al navegar
// ============================================

it('updates url correctly when navigating with wire:navigate', function () {
    createPublicTestData();

    $page = visit('/')
        ->click(__('common.nav.programs'))
        ->wait(1);

    $page->assertPathIs('/programas')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Sin errores de JavaScript tras varias navegaciones
// ============================================

it('has no javascript errors after multiple wire:navigate navigations', function () {
    $data = createPublicTestData();

    $page = visit('/')
        ->assertSee('Erasmus+')
        ->click(__('common.nav.programs'))
        ->wait(1);

    $page->assertPathIs('/programas')
        ->assertNoJavascriptErrors()
        ->click(__('common.nav.calls'))
        ->wait(1);

    $page->assertPathIs('/convocatorias')
        ->assertNoJavascriptErrors()
        ->click(__('common.nav.news'))
        ->wait(1);

    $page->assertPathIs('/noticias')
        ->assertNoJavascriptErrors()
        ->click(__('common.nav.home'))
        ->wait(1);

    $page->assertPathIs('/')
        ->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});
