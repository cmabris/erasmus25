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

use function Tests\Browser\Helpers\assertNoLazyLoading;
use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\ensureRolesExist;
use function Tests\Browser\Helpers\performLogin;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Validación de eager loading en páginas de administración
// ============================================

it('has relations eager loaded in admin calls index', function () {
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

    // Verificar que program, academicYear, creator y updater están eager loaded
    assertNoLazyLoading('Call', 'program', $queries);
    assertNoLazyLoading('Call', 'academicYear', $queries);
    assertNoLazyLoading('Call', 'creator', $queries);
    assertNoLazyLoading('Call', 'updater', $queries);

    $page->assertSee('Convocatorias')
        ->assertNoJavascriptErrors();
});

it('has relations eager loaded in admin news index', function () {
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

    // Verificar que program, author y tags están eager loaded
    assertNoLazyLoading('NewsPost', 'program', $queries);
    assertNoLazyLoading('NewsPost', 'author', $queries);

    $page->assertSee('Noticias')
        ->assertNoJavascriptErrors();
});

it('has roles eager loaded in admin users index', function () {
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

    // Verificar que los roles están eager loaded
    // No debería haber queries individuales para cada usuario cargando sus roles
    assertNoLazyLoading('User', 'roles', $queries);

    $page->assertSee('Usuarios')
        ->assertNoJavascriptErrors();
});

it('has phases and resolutions eager loaded in admin call show', function () {
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

    // Verificar que phases y resolutions están eager loaded
    assertNoLazyLoading('CallPhase', 'call', $queries);
    assertNoLazyLoading('Resolution', 'call', $queries);

    $page->assertSee($call->title)
        ->assertNoJavascriptErrors();
});
