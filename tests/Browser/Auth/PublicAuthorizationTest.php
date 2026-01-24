<?php

use App\Models\Program;

use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\createPublicTestData;
use function Tests\Browser\Helpers\performLogin;

// ============================================
// Test: Usuario no autenticado puede acceder a Home
// ============================================

it('allows guest to access home page', function () {
    Program::factory()->count(1)->create(['is_active' => true]);

    $page = visit('/');

    $page->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Usuario no autenticado puede acceder a listados públicos
// ============================================

it('allows guest to access programas index', function () {
    Program::factory()->create(['name' => 'Guest Program', 'is_active' => true]);

    $page = visit(route('programas.index'));

    $page->assertSee('Guest Program')
        ->assertNoJavascriptErrors();
});

it('allows guest to access convocatorias index', function () {
    $data = createPublicTestData();

    $page = visit(route('convocatorias.index'));

    $page->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();
});

it('allows guest to access noticias index', function () {
    $data = createPublicTestData();

    $page = visit(route('noticias.index'));

    $page->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Usuario autenticado puede acceder a las mismas páginas públicas
// ============================================

it('allows authenticated user to access home and public listings', function () {
    Program::factory()->create(['name' => 'Auth Program', 'is_active' => true]);
    $data = createPublicTestData();
    $user = createAuthTestUser();

    $page = performLogin($user);

    $page->navigate('/')
        ->assertSee('Erasmus+')
        ->assertNoJavascriptErrors();

    $page->navigate(route('programas.index'))
        ->assertSee('Auth Program')
        ->assertNoJavascriptErrors();

    $page->navigate(route('convocatorias.index'))
        ->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    $page->navigate(route('noticias.index'))
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Usuario autenticado puede acceder a detalle de recurso público
// ============================================

it('allows authenticated user to access public resource detail pages', function () {
    $data = createPublicTestData();
    $user = createAuthTestUser();

    $page = performLogin($user);

    $page->navigate(route('programas.show', $data['program']->slug))
        ->assertSee($data['program']->name)
        ->assertNoJavascriptErrors();

    $page->navigate(route('convocatorias.show', $data['call']->slug))
        ->assertSee($data['call']->title)
        ->assertNoJavascriptErrors();

    $page->navigate(route('noticias.show', $data['news']->slug))
        ->assertSee($data['news']->title)
        ->assertNoJavascriptErrors();
});
