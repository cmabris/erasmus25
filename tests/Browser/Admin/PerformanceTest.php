<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Support\Roles;

use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;

// ============================================
// Tests: Tiempos de carga para páginas de administración
// ============================================

it('loads admin dashboard within acceptable time', function () {
    // Crear datos variados
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    Document::factory()->count(3)->create([
        'program_id' => $program->id,
        'is_active' => true,
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

    $startTime = microtime(true);
    $page = visit(route('admin.dashboard'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 3 segundos (3000ms)
    expect($loadTime)->toBeLessThan(3000);

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});

it('loads admin programs index page within acceptable time', function () {
    Program::factory()->count(20)->create(['is_active' => true]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados para programs.*
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $startTime = microtime(true);
    $page = visit(route('admin.programs.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2.5 segundos (2500ms)
    expect($loadTime)->toBeLessThan(2500);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('loads admin calls index page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(20)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $startTime = microtime(true);
    $page = visit(route('admin.calls.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2.5 segundos (2500ms)
    expect($loadTime)->toBeLessThan(2500);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('loads admin call show page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    // Crear fases y resoluciones
    CallPhase::factory()->count(5)->create([
        'call_id' => $call->id,
    ]);

    Resolution::factory()->count(3)->create([
        'call_id' => $call->id,
        'call_phase_id' => CallPhase::where('call_id', $call->id)->first()->id,
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $startTime = microtime(true);
    $page = visit(route('admin.calls.show', $call));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 3 segundos (3000ms) - más complejo por fases y resoluciones
    expect($loadTime)->toBeLessThan(3000);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('loads admin news index page within acceptable time', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    NewsPost::factory()->count(20)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $startTime = microtime(true);
    $page = visit(route('admin.news.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2.5 segundos (2500ms)
    expect($loadTime)->toBeLessThan(2500);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('loads admin users index page within acceptable time', function () {
    User::factory()->count(20)->create();

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    $startTime = microtime(true);
    $page = visit(route('admin.users.index'));
    $endTime = microtime(true);

    $loadTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    // Verificar que la página carga en menos de 2.5 segundos (2500ms)
    expect($loadTime)->toBeLessThan(2500);

    $page->assertSee('Usuarios')
        ->assertNoJavascriptErrors();
});
