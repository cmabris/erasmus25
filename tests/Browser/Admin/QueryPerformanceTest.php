<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use App\Support\Roles;

use function Tests\Browser\Helpers\assertBrowserNoDuplicateQueries;
use function Tests\Browser\Helpers\assertBrowserQueryCountLessThan;
use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\ensureRolesExist;
use function Tests\Browser\Helpers\performLogin;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Número máximo de consultas para páginas de administración
// ============================================

it('executes less than 40 queries on admin dashboard', function () {
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

    startBrowserQueryLog();
    $page = visit(route('admin.dashboard'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 40);

    $page->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});

it('executes less than 30 queries on admin programs index page', function () {
    Program::factory()->count(20)->create(['is_active' => true]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados para programs.*
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.programs.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 30);

    $page->assertSee('Programas')
        ->assertNoJavascriptErrors();
});

it('executes less than 30 queries on admin calls index page', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    Call::factory()->count(20)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.calls.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 30);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('executes less than 35 queries on admin call show page', function () {
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

    startBrowserQueryLog();
    $page = visit(route('admin.calls.show', $call));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 35);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});

it('executes less than 30 queries on admin news index page', function () {
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

    startBrowserQueryLog();
    $page = visit(route('admin.news.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 30);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('executes less than 30 queries on admin users index page', function () {
    User::factory()->count(20)->create();

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.users.index'));
    $queries = stopBrowserQueryLog();

    assertBrowserQueryCountLessThan($queries, 30);

    $page->assertSee('Usuarios')
        ->assertNoJavascriptErrors();
});

// ============================================
// Tests: Detección de N+1 en páginas de administración
// ============================================

it('does not have N+1 when loading admin calls index with relations', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $creator = User::factory()->create();
    $updater = User::factory()->create();

    Call::factory()->count(20)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'created_by' => $creator->id,
        'updated_by' => $updater->id,
    ]);

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.calls.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading admin news index with relations', function () {
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();
    $tags = NewsTag::factory()->count(3)->create();

    NewsPost::factory()->count(20)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ])->each(function ($news) use ($tags) {
        $news->tags()->attach($tags->random(rand(1, 3)));
    });

    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.news.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('does not have N+1 when loading admin users index with roles', function () {
    // Asegurar que los roles existen antes de asignarlos
    ensureRolesExist();

    $users = User::factory()->count(20)->create();

    // Asignar roles a los usuarios
    foreach ($users as $user) {
        $user->assignRole(Roles::VIEWER);
    }

    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    startBrowserQueryLog();
    $page = visit(route('admin.users.index'));
    $queries = stopBrowserQueryLog();

    // Permitir duplicados legítimos
    assertBrowserNoDuplicateQueries($queries, ['activity_log', 'permissions']);

    $page->assertSee('Usuarios')
        ->assertNoJavascriptErrors();
});
