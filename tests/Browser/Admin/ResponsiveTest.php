<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\User;
use App\Support\Roles;

use function Tests\Browser\Helpers\assertNoHorizontalScroll;
use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;

// ============================================
// Fase 2.1: Tests de Dashboard responsive
// ============================================

it('admin dashboard looks good on mobile', function () {
    // Crear datos variados
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    NewsPost::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.dashboard'))
        ->on()->mobile();

    // Verificar que la página carga correctamente
    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);
});

it('admin dashboard looks good on tablet', function () {
    // Crear datos variados
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    NewsPost::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.dashboard'))
        ->resize(768, 1024);

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin dashboard looks good on desktop', function () {
    // Crear datos variados
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    NewsPost::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.dashboard'))
        ->on()->desktop();

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 2.2: Tests de Programs Index (admin) responsive
// ============================================

it('admin programs index looks good on mobile', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.programs.index'))
        ->on()->mobile();

    // Verificar que la página carga correctamente
    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();

    // Verificar que no hay overflow horizontal
    assertNoHorizontalScroll($page);
});

it('admin programs index looks good on tablet', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.programs.index'))
        ->resize(768, 1024);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin programs index looks good on desktop', function () {
    Program::factory()->count(10)->create(['is_active' => true]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.programs.index'))
        ->on()->desktop();

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 2.2: Tests de Calls Index (admin) responsive
// ============================================

it('admin calls index looks good on mobile', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.calls.index'))
        ->on()->mobile();

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin calls index looks good on tablet', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.calls.index'))
        ->resize(768, 1024);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin calls index looks good on desktop', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.calls.index'))
        ->on()->desktop();

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

// ============================================
// Fase 2.2: Tests de News Index (admin) responsive
// ============================================

it('admin news index looks good on mobile', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.news.index'))
        ->on()->mobile();

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin news index looks good on tablet', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.news.index'))
        ->resize(768, 1024);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});

it('admin news index looks good on desktop', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(10)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $page = visit(route('admin.news.index'))
        ->on()->desktop();

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();

    assertNoHorizontalScroll($page);
});
