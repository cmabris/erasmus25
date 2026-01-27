<?php

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use App\Support\Roles;
use Illuminate\Support\Facades\Cache;

use function Tests\Browser\Helpers\compareQueryCountsWithCache;
use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\getBrowserQueryCount;
use function Tests\Browser\Helpers\performLogin;
use function Tests\Browser\Helpers\startBrowserQueryLog;
use function Tests\Browser\Helpers\stopBrowserQueryLog;

// ============================================
// Tests: Caché en páginas de administración
// ============================================

it('cache reduces queries on second load of admin dashboard', function () {
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

    // Limpiar caché antes del test
    Cache::flush();

    // Primera carga
    startBrowserQueryLog();
    $page1 = visit(route('admin.dashboard'));
    $queriesWithoutCache = stopBrowserQueryLog();

    // Segunda carga (debería usar caché)
    startBrowserQueryLog();
    $page2 = visit(route('admin.dashboard'));
    $queriesWithCache = stopBrowserQueryLog();

    // Verificar que la segunda carga tiene menos o igual número de queries
    compareQueryCountsWithCache($queriesWithoutCache, $queriesWithCache);

    $page2->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});

it('cache for configurations works in administration', function () {
    // Usar SUPER_ADMIN porque ADMIN requiere wildcards habilitados
    $user = createAuthTestUser([], Roles::SUPER_ADMIN);
    performLogin($user);

    // Limpiar caché
    Cache::flush();

    // Primera carga - debería ejecutar queries para configuraciones
    startBrowserQueryLog();
    $page1 = visit(route('admin.dashboard'));
    $queriesFirst = stopBrowserQueryLog();

    // Segunda carga - debería usar caché de configuraciones
    startBrowserQueryLog();
    $page2 = visit(route('admin.dashboard'));
    $queriesSecond = stopBrowserQueryLog();

    // Verificar que la segunda carga tiene menos o igual queries
    $countFirst = getBrowserQueryCount($queriesFirst);
    $countSecond = getBrowserQueryCount($queriesSecond);

    expect($countSecond)->toBeLessThanOrEqual($countFirst, 'Second load should have fewer or equal queries due to configuration cache');

    $page2->assertSee('Dashboard')
        ->assertNoJavascriptErrors();
});
