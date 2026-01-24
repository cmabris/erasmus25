<?php

use App\Models\Program;
use Illuminate\Support\Facades\App;

use function Tests\Browser\Helpers\createGlobalSearchTestData;

beforeEach(function () {
    App::setLocale('es');
    createGlobalSearchTestData();
});

// ============================================
// Test: Verificar página de búsqueda
// ============================================

it('displays the search page with title, description and initial state', function () {
    $page = visit(route('search'));

    $page->assertSee(__('common.search.global_title'))
        ->assertSee(__('common.search.global_description'))
        ->assertSee(__('common.search.start_search'))
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Búsqueda en tiempo real — resultados de programas
// ============================================

it('shows program results when searching in real time', function () {
    $page = visit(route('search'))
        ->fill('query', 'Movilidad')
        ->wait(1);

    $page->assertSee('Programa de Movilidad')
        ->assertSee(__('common.search.programs'));
});

// ============================================
// Test: Búsqueda en tiempo real — convocatorias, noticias, documentos
// ============================================

it('shows calls, news and document results when searching', function () {
    $page = visit(route('search'))
        ->fill('query', 'Movilidad')
        ->wait(1);

    $page->assertSee('Convocatoria de Movilidad')
        ->assertSee(__('common.search.calls'))
        ->assertSee('Noticia sobre Movilidad')
        ->assertSee(__('common.search.news'))
        ->assertSee('Documento de Movilidad')
        ->assertSee(__('common.search.documents'));
});

// ============================================
// Test: Resultados vacíos
// ============================================

it('shows no results message when query has no matches', function () {
    $page = visit(route('search'))
        ->fill('query', 'XyZAbC123Nada')
        ->wait(1);

    $page->assertSee(__('common.search.no_results'))
        ->assertSee(__('common.search.no_results_message'));
});

// ============================================
// Test: Filtros avanzados — mostrar/ocultar panel
// ============================================

it('toggles advanced filters panel on click', function () {
    $page = visit(route('search'));

    $page->click(__('common.search.advanced_filters'))
        ->assertSee(__('common.search.content_types'));

    $page->click(__('common.search.advanced_filters'))
        ->assertDontSee(__('common.search.content_types'));
});

// ============================================
// Test: Filtro por programa
// ============================================

it('filters results by program when selecting from advanced filters', function () {
    Program::factory()->create([
        'name' => 'Otro Programa',
        'code' => 'KA2',
        'is_active' => true,
    ]);

    $page = visit(route('search'))
        ->fill('query', 'Movilidad')
        ->wait(1)
        ->click(__('common.search.advanced_filters'))
        ->wait(1);

    $page->select('#program-filter', 'Programa de Movilidad')
        ->wait(1);

    $page->assertSee('Programa de Movilidad')
        ->assertSee('Convocatoria de Movilidad')
        ->assertSee('Noticia sobre Movilidad');
});

// ============================================
// Test: Botón «Limpiar búsqueda»
// ============================================

it('clears search and shows initial state when clicking clear search', function () {
    $page = visit(route('search'))
        ->fill('query', 'algo')
        ->wait(1)
        ->click(__('common.search.clear_search'));

    $page->assertSee(__('common.search.start_search'));
});

// ============================================
// Test: Navegación a un resultado
// ============================================

it('navigates to program detail when clicking a result link', function () {
    $page = visit(route('search'))
        ->fill('query', 'Movilidad')
        ->wait(1);

    $page->assertSee('Programa de Movilidad')
        ->click('Programa de Movilidad')
        ->wait(1);

    $page->assertPathBeginsWith('/programas/')
        ->assertSee('Programa de Movilidad');
});

// ============================================
// Test: Sin errores de JavaScript en la página de búsqueda
// ============================================

it('has no javascript errors on search page', function () {
    $page = visit(route('search'));

    $page->assertNoJavascriptErrors();
});

it('has no javascript errors after search and opening filters', function () {
    $page = visit(route('search'))
        ->fill('query', 'Movilidad')
        ->wait(1)
        ->click(__('common.search.advanced_filters'))
        ->wait(1);

    $page->assertNoJavascriptErrors();
});
