<?php

use Database\Seeders\LanguagesSeeder;
use Illuminate\Support\Facades\App;

use function Tests\Browser\Helpers\createPublicTestData;

beforeEach(function () {
    App::setLocale('es');
    (new LanguagesSeeder)->run();
});

// ============================================
// 2.1. Menú móvil
// ============================================

it('opens mobile menu when clicking hamburger button', function () {
    createPublicTestData();

    $page = visit('/')
        ->withLocale('es')
        ->on()->mobile()
        ->click(__('common.nav.open_menu'));

    $page->assertSee(__('common.nav.programs'))
        ->assertSee(__('common.nav.calls'))
        ->assertNoJavascriptErrors();
});

it('closes mobile menu when clicking outside', function () {
    createPublicTestData();

    $page = visit('/')
        ->withLocale('es')
        ->on()->mobile()
        ->click(__('common.nav.open_menu'))
        ->assertSee(__('common.nav.programs'));

    $page->click('[aria-label="'.__('common.nav.home').'"]')
        ->wait(0.5);

    $page->assertMissing('[role="menu"] a[href*="programas"]')
        ->assertNoJavascriptErrors();
});

it('navigates from mobile menu and closes it', function () {
    $data = createPublicTestData();

    $page = visit('/')
        ->withLocale('es')
        ->on()->mobile()
        ->click(__('common.nav.open_menu'))
        ->wait(0.4)
        ->click('[role="menu"] a[href*="programas"]')
        ->wait(1);

    $page->assertPathIs('/programas')
        ->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();
});

it('mobile menu links navigate to correct routes', function () {
    $data = createPublicTestData();

    $page = visit('/')
        ->withLocale('es')
        ->on()->mobile()
        ->click(__('common.nav.open_menu'))
        ->wait(0.4)
        ->click('[role="menu"] a[href*="convocatorias"]')
        ->wait(1);

    $page->assertPathIs('/convocatorias')
        ->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    $page = visit('/')
        ->withLocale('es')
        ->on()->mobile()
        ->click(__('common.nav.open_menu'))
        ->wait(0.4)
        ->click('[role="menu"] a[href*="noticias"]')
        ->wait(1);

    $page->assertPathIs('/noticias')
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// 2.2. Language Switcher (dropdown, desktop)
// ============================================

it('opens language dropdown when clicking switcher button', function () {
    createPublicTestData();

    $label = __('common.language.change');
    $page = visit('/')
        ->withLocale('es')
        ->click('[aria-label="'.$label.'"]');

    $page->assertSee('English')
        ->assertNoJavascriptErrors();
});

it('changes language and redirects with new locale', function () {
    $data = createPublicTestData();

    $label = __('common.language.change');
    $page = visit(route('noticias.index'))
        ->withLocale('es')
        ->assertSee(__('common.nav.news'))
        ->click('[aria-label="'.$label.'"]')
        ->click('English')
        ->wait(1);

    $page->assertPathIs('/noticias')
        ->assertSee('News')
        ->assertNoJavascriptErrors();
});

it('closes language dropdown when clicking outside', function () {
    createPublicTestData();

    $label = __('common.language.change');
    $page = visit('/')
        ->withLocale('es')
        ->click('[aria-label="'.$label.'"]')
        ->assertSee('English');

    $page->click(__('common.home.hero_title'))
        ->wait(0.5);

    $page->assertDontSee('English')
        ->assertNoJavascriptErrors();
});

// ============================================
// 2.3. Modales, Tabs y Tooltips en área pública
// ============================================

it('documents that modals tabs and tooltips are not used in public area', function () {
    // Modales, flux:tabs y tooltips no se usan en el área pública.
    // El acordeón/panel de Filtros avanzados queda cubierto en GlobalSearchTest.
    // Ver paso-3.11.5-plan.md sección 2.3.
    expect(true)->toBeTrue();
});
