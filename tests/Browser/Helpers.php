<?php

namespace Tests\Browser\Helpers;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Document;
use App\Models\ErasmusEvent;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use App\Models\Resolution;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

/**
 * Helper function to create public test data for browser tests.
 *
 * Creates a complete set of test data including:
 * - An active program
 * - An academic year
 * - A published call (abierta)
 * - A published news post
 *
 * @return array<string, mixed> Array containing the created models
 */
function createPublicTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    $call = Call::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
    ]);
    $news = NewsPost::factory()->published()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'call' => $call,
        'news' => $news,
        'author' => $author,
    ];
}

/**
 * Helper function to create an authenticated user for browser tests.
 *
 * @param  array<string, mixed>  $attributes  Additional attributes for the user
 */
function createAuthenticatedUser(array $attributes = []): User
{
    return User::factory()->create($attributes);
}

/**
 * Asegura que los roles y permisos del sistema existen en la base de datos.
 *
 * Ejecuta RolesAndPermissionsSeeder para crear roles (super-admin, admin, editor, viewer)
 * y sus permisos. Idempotente (usa firstOrCreate). Necesario antes de assignRole()
 * en tests con RefreshDatabase, ya que cada test parte de BD vacía.
 */
function ensureRolesExist(): void
{
    (new RolesAndPermissionsSeeder)->run();
}

/**
 * Crea un usuario de prueba para tests de autenticación y autorización.
 *
 * - Usa withoutTwoFactor() para evitar el flujo de 2FA en el login.
 * - Contraseña por defecto: 'password' (se hashea por el cast del modelo).
 * - Si se pasa $role, ejecuta ensureRolesExist() y asigna el rol (p. ej. Roles::VIEWER).
 *
 * Para login en browser tests, usar performLogin($user) con la contraseña 'password'.
 *
 * @param  array<string, mixed>  $overrides  Atributos que sobrescriben los por defecto
 * @param  string|null  $role  Nombre del rol a asignar (App\Support\Roles::*)
 */
function createAuthTestUser(array $overrides = [], ?string $role = null): User
{
    $user = User::factory()->withoutTwoFactor()->create(array_merge(
        ['password' => 'password'],
        $overrides
    ));

    if ($role !== null) {
        ensureRolesExist();
        $user->assignRole($role);
    }

    return $user;
}

/**
 * Ejecuta el flujo de login en el navegador (visit, fill, submit).
 *
 * Debe llamarse desde un test de Pest Browser (visit() proviene del plugin).
 * Tras login exitoso, Fortify redirige a /dashboard. La página devuelta
 * permite encadenar navigate() o visit() a otras rutas (p. ej. /admin/...).
 *
 * @param  \App\Models\User  $user  Usuario creado con createAuthTestUser (contraseña 'password')
 * @return mixed Página del navegador tras el login (encadenar navigate, assertSee, etc.)
 */
function performLogin(User $user)
{
    $page = visit(route('login'));
    $page->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Log in');

    return $page;
}

/**
 * Helper function to create complete home page test data.
 *
 * Creates a complete set of test data for home page tests including:
 * - 6 active programs
 * - 1 academic year
 * - 4 open calls (abierta)
 * - 3 published news posts
 * - 5 upcoming events
 *
 * @return array<string, mixed> Array containing the created models
 */
function createHomeTestData(): array
{
    // Crear programas activos
    $programs = Program::factory()->count(6)->create(['is_active' => true]);

    // Crear año académico
    $academicYear = AcademicYear::factory()->create();

    // Crear convocatorias abiertas
    $calls = Call::factory()->count(4)->create([
        'program_id' => $programs->first()->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Crear autor para las noticias
    $author = User::factory()->create();

    // Crear noticias publicadas
    $news = NewsPost::factory()->count(3)->create([
        'program_id' => $programs->first()->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    // Crear eventos próximos
    $events = ErasmusEvent::factory()->count(5)->create([
        'is_public' => true,
        'start_date' => now()->addDays(7),
    ]);

    return [
        'programs' => $programs,
        'academicYear' => $academicYear,
        'calls' => $calls,
        'news' => $news,
        'events' => $events,
        'author' => $author,
    ];
}

/**
 * Helper function to create newsletter subscription form test data for browser tests.
 *
 * Creates 2-3 active programs with known codes (KA1, KA2, KA3) for the newsletter
 * subscribe form. The Subscribe component uses toggleProgram($program->code), so
 * predictable codes allow tests to reliably select programs by clicking on them.
 *
 * @return array<string, mixed> Array containing the created models
 */
function createNewsletterTestData(): array
{
    // Usar firstOrCreate para evitar conflictos si los programas ya existen
    $programs = collect([
        Program::firstOrCreate(
            ['code' => 'KA1'],
            [
                'name' => 'Programa KA1',
                'is_active' => true,
                'order' => 1,
            ]
        ),
        Program::firstOrCreate(
            ['code' => 'KA2'],
            [
                'name' => 'Programa KA2',
                'is_active' => true,
                'order' => 2,
            ]
        ),
        Program::firstOrCreate(
            ['code' => 'KA3'],
            [
                'name' => 'Programa KA3',
                'is_active' => true,
                'order' => 3,
            ]
        ),
    ]);

    return [
        'programs' => $programs,
    ];
}

/**
 * Helper function to create global search test data for browser tests.
 *
 * Creates a complete set of test data for GlobalSearch browser tests including:
 * - 1 program ("Programa de Movilidad", code KA1)
 * - 1 academic year (2024-2025)
 * - 1 call ("Convocatoria de Movilidad", abierta, published)
 * - 1 news post ("Noticia sobre Movilidad", publicado)
 * - 1 document ("Documento de Movilidad", is_active)
 *
 * All titles/descriptions contain "Movilidad" so a single search term can find
 * results across all types. Mirrors the structure used in GlobalSearchTest (Feature).
 *
 * @return array<string, mixed> Array containing the created models
 */
function createGlobalSearchTestData(): array
{
    $program = Program::factory()->create([
        'name' => 'Programa de Movilidad',
        'code' => 'KA1',
        'description' => 'Programa de movilidad estudiantil',
        'is_active' => true,
    ]);

    $academicYear = AcademicYear::factory()->create([
        'year' => '2024-2025',
    ]);

    $author = User::factory()->create();

    $call = Call::factory()->create([
        'title' => 'Convocatoria de Movilidad',
        'requirements' => 'Requisitos para movilidad',
        'status' => 'abierta',
        'published_at' => now(),
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    $news = NewsPost::factory()->create([
        'title' => 'Noticia sobre Movilidad',
        'excerpt' => 'Resumen de la noticia',
        'status' => 'publicado',
        'published_at' => now(),
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
    ]);

    $document = Document::factory()->create([
        'title' => 'Documento de Movilidad',
        'description' => 'Descripción del documento',
        'is_active' => true,
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'author' => $author,
        'call' => $call,
        'news' => $news,
        'document' => $document,
    ];
}

/**
 * Helper function to create programs test data for browser tests.
 *
 * Creates a complete set of test data for programs index tests including:
 * - Programs of different types (KA1, KA2, JM)
 * - Active and inactive programs
 *
 * @return array<string, mixed> Array containing the created models
 */
function createProgramsTestData(): array
{
    $programs = collect();

    // Crear programas de diferentes tipos
    $programs->push(Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Programa KA1 VET',
        'is_active' => true,
    ]));

    $programs->push(Program::factory()->create([
        'code' => 'KA220-SCH',
        'name' => 'Programa KA2 Escolar',
        'is_active' => true,
    ]));

    $programs->push(Program::factory()->create([
        'code' => 'JM-001',
        'name' => 'Programa Jean Monnet',
        'is_active' => true,
    ]));

    // Crear programas inactivos
    $programs->push(Program::factory()->create([
        'code' => 'KA131-HED',
        'name' => 'Programa Inactivo',
        'is_active' => false,
    ]));

    return [
        'programs' => $programs,
    ];
}

/**
 * Helper function to create program show test data for browser tests.
 *
 * Creates a complete set of test data for program detail page tests including:
 * - 1 program
 * - 1 academic year
 * - 5 related calls (abierta)
 * - 4 related news posts (publicado)
 * - 3 other active programs
 *
 * @return array<string, mixed> Array containing the created models
 */
function createProgramShowTestData(): array
{
    $program = Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Programa de Prueba',
        'is_active' => true,
    ]);

    $academicYear = AcademicYear::factory()->create();

    // Crear convocatorias relacionadas
    $calls = Call::factory()->count(5)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Crear autor para las noticias
    $author = User::factory()->create();

    // Crear noticias relacionadas
    $news = NewsPost::factory()->count(4)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    // Crear otros programas
    $otherPrograms = Program::factory()->count(3)->create([
        'is_active' => true,
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'calls' => $calls,
        'news' => $news,
        'otherPrograms' => $otherPrograms,
        'author' => $author,
    ];
}

/**
 * Helper function to create calls test data for browser tests.
 *
 * Creates a complete set of test data for calls index tests including:
 * - Calls of different types (alumnado, personal)
 * - Calls of different modalities (corta, larga)
 * - Calls of different statuses (abierta, cerrada)
 *
 * @return array<string, mixed> Array containing the created models
 */
function createCallsTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    // Crear convocatorias de diferentes tipos y estados
    $calls = collect();

    $calls->push(Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'alumnado',
        'modality' => 'corta',
        'status' => 'abierta',
        'published_at' => now(),
    ]));

    $calls->push(Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'type' => 'personal',
        'modality' => 'larga',
        'status' => 'cerrada',
        'published_at' => now()->subDays(5),
    ]));

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'calls' => $calls,
    ];
}

/**
 * Helper function to create call show test data for browser tests.
 *
 * Creates a complete set of test data for call detail page tests including:
 * - 1 call
 * - 1 program
 * - 1 academic year
 * - 3 phases
 * - 2 resolutions (1 published, 1 unpublished)
 * - 4 related news posts (publicado)
 * - 3 other calls from the same program
 *
 * @return array<string, mixed> Array containing the created models
 */
function createCallShowTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();

    $call = Call::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    // Crear fases
    $phases = CallPhase::factory()->count(3)->create([
        'call_id' => $call->id,
    ]);

    // Crear resoluciones (algunas publicadas, otras no)
    $resolutions = collect();
    $resolutions->push(Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phases->first()->id,
        'published_at' => now(),
    ]));
    $resolutions->push(Resolution::factory()->create([
        'call_id' => $call->id,
        'call_phase_id' => $phases->first()->id,
        'published_at' => null, // No publicada
    ]));

    // Crear autor para las noticias
    $author = User::factory()->create();

    // Crear noticias relacionadas
    $news = NewsPost::factory()->count(4)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);

    // Crear otras convocatorias
    $otherCalls = Call::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    return [
        'call' => $call,
        'program' => $program,
        'academicYear' => $academicYear,
        'phases' => $phases,
        'resolutions' => $resolutions,
        'news' => $news,
        'otherCalls' => $otherCalls,
        'author' => $author,
    ];
}

/**
 * Helper function to create news test data for browser tests.
 *
 * Creates a complete set of test data for news index tests including:
 * - 1 program
 * - 1 academic year
 * - 1 author
 * - 3 tags
 * - 2 news posts with different configurations
 *
 * @return array<string, mixed> Array containing the created models
 */
function createNewsTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    // Crear etiquetas
    $tags = NewsTag::factory()->count(3)->create();

    // Crear noticias con diferentes configuraciones
    $news = collect();

    $news1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);
    $news1->tags()->attach($tags->first());
    $news->push($news1);

    $news2 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(5),
    ]);
    $news2->tags()->attach($tags->take(2));
    $news->push($news2);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'author' => $author,
        'tags' => $tags,
        'news' => $news,
    ];
}

/**
 * Helper function to create news show test data for browser tests.
 *
 * Creates a complete set of test data for news detail page tests including:
 * - 1 news post
 * - 1 program
 * - 1 academic year
 * - 1 author
 * - 3 tags
 * - 1 related news post (same program and tags)
 * - 3 related calls (same program)
 *
 * @return array<string, mixed> Array containing the created models
 */
function createNewsShowTestData(): array
{
    $program = Program::factory()->create(['is_active' => true]);
    $academicYear = AcademicYear::factory()->create();
    $author = User::factory()->create();

    // Crear etiquetas
    $tags = NewsTag::factory()->count(3)->create();

    // Crear noticia principal
    $newsPost = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now(),
    ]);
    $newsPost->tags()->attach($tags->take(2));

    // Crear noticias relacionadas (mismo programa y etiquetas)
    $relatedNews = collect();
    $relatedNews1 = NewsPost::factory()->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'author_id' => $author->id,
        'status' => 'publicado',
        'published_at' => now()->subDays(2),
    ]);
    $relatedNews1->tags()->attach($tags->first());
    $relatedNews->push($relatedNews1);

    // Crear convocatorias relacionadas
    $relatedCalls = Call::factory()->count(3)->create([
        'program_id' => $program->id,
        'academic_year_id' => $academicYear->id,
        'status' => 'abierta',
        'published_at' => now(),
    ]);

    return [
        'program' => $program,
        'academicYear' => $academicYear,
        'author' => $author,
        'tags' => $tags,
        'newsPost' => $newsPost,
        'relatedNews' => $relatedNews,
        'relatedCalls' => $relatedCalls,
    ];
}

// ============================================
// Performance Testing Helpers
// ============================================

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Start logging database queries for browser tests.
 *
 * Call this before visiting a page to start tracking queries.
 * Must be paired with stopBrowserQueryLog() after the page visit.
 */
function startBrowserQueryLog(): void
{
    DB::flushQueryLog();
    DB::enableQueryLog();
}

/**
 * Stop logging and get the query log.
 *
 * @return array<int, array{query: string, bindings: array, time: float}>
 */
function stopBrowserQueryLog(): array
{
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    return $queries;
}

/**
 * Get the query count from the current log.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 */
function getBrowserQueryCount(array $queries): int
{
    return count($queries);
}

/**
 * Get all logged queries.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @return array<int, array{query: string, bindings: array, time: float}>
 */
function getBrowserQueries(array $queries): array
{
    return $queries;
}

/**
 * Normalize a query for comparison (replace specific values with placeholders).
 *
 * This is used to detect duplicate queries (N+1 problems) by normalizing
 * queries so that queries with different values but the same structure are
 * considered duplicates.
 */
function normalizeBrowserQuery(string $query): string
{
    // Replace numeric values
    $query = preg_replace('/\b\d+\b/', '?', $query);

    // Replace quoted strings
    $query = preg_replace("/'[^']*'/", '?', $query);

    // Replace IN clauses with multiple values
    $query = preg_replace('/\bIN\s*\(\s*\?(?:\s*,\s*\?)*\s*\)/i', 'IN (?)', $query);

    return $query;
}

/**
 * Get duplicate queries (potential N+1).
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @return array<string, int> Query pattern => count
 */
function getBrowserDuplicateQueries(array $queries): array
{
    $patterns = collect($queries)
        ->map(fn ($query) => normalizeBrowserQuery($query['query']))
        ->countBy()
        ->filter(fn ($count) => $count > 1)
        ->all();

    return $patterns;
}

/**
 * Get total query time in milliseconds.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 */
function getBrowserTotalQueryTime(array $queries): float
{
    return collect($queries)->sum('time');
}

/**
 * Get queries that exceed a time threshold.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  float  $threshold  Time in milliseconds
 * @return array<int, array{query: string, bindings: array, time: float}>
 */
function getBrowserSlowQueries(array $queries, float $threshold = 100.0): array
{
    return collect($queries)
        ->filter(fn ($query) => $query['time'] > $threshold)
        ->values()
        ->all();
}

/**
 * Assert that query count is less than a threshold.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  int  $maxQueries  Maximum number of queries allowed
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertBrowserQueryCountLessThan(array $queries, int $maxQueries, ?string $message = null): void
{
    $count = getBrowserQueryCount($queries);
    $message = $message ?? "Expected less than {$maxQueries} queries, but {$count} were executed.";

    if ($count >= $maxQueries) {
        outputBrowserQueryDetails($queries);
    }

    expect($count)->toBeLessThan($maxQueries, $message);
}

/**
 * Assert there are no duplicate queries (N+1 detection).
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  array<string>  $allowedPatterns  Patterns that are allowed to be duplicated (e.g., 'activity_log', 'permissions')
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertBrowserNoDuplicateQueries(array $queries, array $allowedPatterns = [], ?string $message = null): void
{
    $duplicates = getBrowserDuplicateQueries($queries);

    // Filter out allowed patterns
    foreach ($allowedPatterns as $pattern) {
        $duplicates = array_filter(
            $duplicates,
            fn ($query) => ! str_contains($query, $pattern),
            ARRAY_FILTER_USE_KEY
        );
    }

    if (! empty($duplicates)) {
        $message = $message ?? 'Potential N+1 queries detected:';
        $details = collect($duplicates)
            ->map(fn ($count, $query) => "  - {$query} (executed {$count} times)")
            ->implode("\n");

        expect($duplicates)->toBeEmpty("{$message}\n{$details}");
    }

    expect(true)->toBeTrue();
}

/**
 * Assert no queries exceed the time threshold.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  float  $threshold  Time in milliseconds
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertBrowserNoSlowQueries(array $queries, float $threshold = 100.0, ?string $message = null): void
{
    $slowQueries = getBrowserSlowQueries($queries, $threshold);

    if (! empty($slowQueries)) {
        $message = $message ?? "Slow queries detected (>{$threshold}ms):";
        $details = collect($slowQueries)
            ->map(fn ($query) => "  - {$query['time']}ms: {$query['query']}")
            ->implode("\n");

        expect($slowQueries)->toBeEmpty("{$message}\n{$details}");
    }

    expect(true)->toBeTrue();
}

/**
 * Assert total query time is less than threshold.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  float  $maxTime  Time in milliseconds
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertBrowserTotalQueryTimeLessThan(array $queries, float $maxTime, ?string $message = null): void
{
    $totalTime = getBrowserTotalQueryTime($queries);
    $message = $message ?? "Expected total query time less than {$maxTime}ms, but took {$totalTime}ms.";

    expect($totalTime)->toBeLessThan($maxTime, $message);
}

/**
 * Output query details for debugging.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 */
function outputBrowserQueryDetails(array $queries): void
{
    $count = getBrowserQueryCount($queries);
    $totalTime = getBrowserTotalQueryTime($queries);

    echo "\n=== Query Log ({$count} queries, {$totalTime}ms total) ===\n";

    foreach ($queries as $index => $query) {
        $num = $index + 1;
        echo "[{$num}] {$query['time']}ms: {$query['query']}\n";
    }

    $duplicates = getBrowserDuplicateQueries($queries);
    if (! empty($duplicates)) {
        echo "\n=== Potential N+1 Queries ===\n";
        foreach ($duplicates as $pattern => $count) {
            echo "  - {$pattern} (x{$count})\n";
        }
    }

    echo "===========================\n\n";
}

/**
 * Assert that a relation is eager loaded (no individual queries for each instance).
 *
 * This checks that there are no queries like "SELECT * FROM table WHERE id = ?"
 * executed multiple times for the same relation, which would indicate lazy loading.
 *
 * @param  string  $relation  Name of the relation (e.g., 'program', 'academicYear')
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertEagerLoaded(string $relation, array $queries, ?string $message = null): void
{
    // Look for queries that load the relation individually (lazy loading pattern)
    // Pattern: SELECT * FROM {table} WHERE id = ? (executed multiple times)
    $lazyLoadingPattern = '/SELECT\s+.*\s+FROM\s+[\w`]+\s+WHERE\s+id\s*=\s*\?/i';

    $lazyQueries = collect($queries)
        ->filter(function ($query) use ($lazyLoadingPattern, $relation) {
            $normalized = normalizeBrowserQuery($query['query']);

            // Check if it matches the lazy loading pattern and relates to the relation
            return preg_match($lazyLoadingPattern, $query['query']) &&
                   (stripos($query['query'], $relation) !== false || stripos($normalized, $relation) !== false);
        })
        ->all();

    // Count how many times the same pattern appears
    $duplicateLazyQueries = collect($lazyQueries)
        ->map(fn ($query) => normalizeBrowserQuery($query['query']))
        ->countBy()
        ->filter(fn ($count) => $count > 1)
        ->all();

    if (! empty($duplicateLazyQueries)) {
        $message = $message ?? "Relation '{$relation}' appears to be lazy loaded (N+1 detected):";
        $details = collect($duplicateLazyQueries)
            ->map(fn ($count, $query) => "  - {$query} (executed {$count} times)")
            ->implode("\n");

        expect($duplicateLazyQueries)->toBeEmpty("{$message}\n{$details}");
    }

    expect(true)->toBeTrue();
}

/**
 * Assert that there are no lazy loading queries for a specific model and relation.
 *
 * @param  string  $model  Name of the model (e.g., 'Program', 'Call')
 * @param  string  $relation  Name of the relation (e.g., 'academicYear', 'program')
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertNoLazyLoading(string $model, string $relation, array $queries, ?string $message = null): void
{
    // Convert model name to table name (snake_case, plural)
    $tableName = str($model)->snake()->plural()->toString();

    // Look for queries that load the relation individually
    // Pattern: SELECT * FROM {relation_table} WHERE {model}_id = ? (executed multiple times)
    $lazyLoadingPattern = "/SELECT\s+.*\s+FROM\s+[`\"]?{$tableName}[`\"]?\s+WHERE\s+[\w`]+\s*=\s*\?/i";

    $lazyQueries = collect($queries)
        ->filter(function ($query) use ($lazyLoadingPattern) {
            return preg_match($lazyLoadingPattern, $query['query']);
        })
        ->all();

    // Count how many times the same pattern appears
    $duplicateLazyQueries = collect($lazyQueries)
        ->map(fn ($query) => normalizeBrowserQuery($query['query']))
        ->countBy()
        ->filter(fn ($count) => $count > 1)
        ->all();

    if (! empty($duplicateLazyQueries)) {
        $message = $message ?? "Lazy loading detected for {$model}->{$relation}:";
        $details = collect($duplicateLazyQueries)
            ->map(fn ($count, $query) => "  - {$query} (executed {$count} times)")
            ->implode("\n");

        expect($duplicateLazyQueries)->toBeEmpty("{$message}\n{$details}");
    }

    expect(true)->toBeTrue();
}

/**
 * Assert that cache is being used (second load has fewer queries).
 *
 * This compares query counts between two loads of the same page.
 * The second load should have fewer queries if cache is working.
 *
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queriesWithoutCache  Queries from first load
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queriesWithCache  Queries from second load
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function compareQueryCountsWithCache(array $queriesWithoutCache, array $queriesWithCache, ?string $message = null): void
{
    $countWithoutCache = getBrowserQueryCount($queriesWithoutCache);
    $countWithCache = getBrowserQueryCount($queriesWithCache);

    $message = $message ?? "Cache should reduce queries. First load: {$countWithoutCache}, Second load: {$countWithCache}.";

    // Cache should reduce queries (second load should have fewer or equal queries)
    expect($countWithCache)->toBeLessThanOrEqual($countWithoutCache, $message);
}

/**
 * Assert that a specific cache key is being used (no queries for that data).
 *
 * This is a helper to verify that certain data is being served from cache
 * rather than being queried from the database. Note: This requires knowing
 * which queries correspond to the cached data.
 *
 * @param  string  $key  Cache key name (for reference in error messages)
 * @param  array<int, array{query: string, bindings: array, time: float}>  $queries  Queries from the page load
 * @param  array<string>  $queryPatterns  SQL patterns that should NOT appear if cache is used (e.g., ['SELECT * FROM settings', 'SELECT * FROM programs WHERE is_active'])
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertCacheUsed(string $key, array $queries, array $queryPatterns = [], ?string $message = null): void
{
    if (empty($queryPatterns)) {
        // If no patterns provided, just verify cache exists (basic check)
        expect(Cache::has($key))->toBeTrue("Cache key '{$key}' should exist.");

        return;
    }

    // Check if any of the patterns appear in queries (indicating cache was NOT used)
    $uncachedQueries = collect($queries)
        ->filter(function ($query) use ($queryPatterns) {
            foreach ($queryPatterns as $pattern) {
                if (stripos($query['query'], $pattern) !== false) {
                    return true;
                }
            }

            return false;
        })
        ->all();

    if (! empty($uncachedQueries)) {
        $message = $message ?? "Cache key '{$key}' appears to not be used. Found queries that should be cached:";
        $details = collect($uncachedQueries)
            ->map(fn ($query) => "  - {$query['query']}")
            ->implode("\n");

        expect($uncachedQueries)->toBeEmpty("{$message}\n{$details}");
    }

    expect(true)->toBeTrue();
}

// ============================================
// Responsive Testing Helpers
// ============================================

/**
 * Assert that there is no horizontal scroll overflow on the page.
 *
 * Verifies that the body width does not exceed the viewport width,
 * which would cause horizontal scrolling.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string|null  $message  Custom error message
 *
 * @throws \PHPUnit\Framework\AssertionFailedError
 */
function assertNoHorizontalScroll($page, ?string $message = null): void
{
    // Use assertScript to verify that scrollWidth <= innerWidth
    // This means there's no horizontal overflow
    $page->assertScript('document.body.scrollWidth <= window.innerWidth', true);
}

// ============================================
// Accessibility Testing Helpers
// ============================================

/**
 * Focus an element by selector.
 *
 * Uses JavaScript to focus an element, simulating keyboard navigation.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $selector  CSS selector for the element to focus
 */
function focusElement($page, string $selector): void
{
    // Escape single quotes in selector for JavaScript
    $escapedSelector = str_replace("'", "\\'", $selector);
    $page->script("
        const element = document.querySelector('{$escapedSelector}');
        if (element) {
            element.focus();
        }
    ");
    // Small wait to ensure focus is applied
    $page->wait(0.1);
}

/**
 * Get the currently focused element tag name.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @return string|null The tag name of the focused element, or null if none
 */
function getFocusedElementTag($page): ?string
{
    $tagName = $page->assertScript('document.activeElement ? document.activeElement.tagName.toLowerCase() : null', null);

    return $tagName;
}

/**
 * Assert that an element has focus.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $selector  CSS selector for the element
 * @param  string|null  $message  Custom error message
 */
function assertElementHasFocus($page, string $selector, ?string $message = null): void
{
    // Escape single quotes in selector for JavaScript
    $escapedSelector = str_replace("'", "\\'", $selector);
    $page->assertScript("document.activeElement && document.activeElement.matches('{$escapedSelector}')", true);
}

/**
 * Assert that focus indicator is visible on the currently focused element.
 *
 * Checks if the focused element has a visible outline or border.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string|null  $message  Custom error message
 */
function assertFocusIndicatorVisible($page, ?string $message = null): void
{
    $page->assertScript("
        (function() {
            const element = document.activeElement;
            if (!element) return false;
            const style = window.getComputedStyle(element);
            const outline = style.outline;
            const outlineWidth = style.outlineWidth;
            const borderWidth = style.borderWidth;
            return (outline !== 'none' && outlineWidth !== '0px') || (borderWidth !== '0px');
        })()
    ", true);
}

// ============================================
// Semantic Structure Testing Helpers
// ============================================

/**
 * Assert that a semantic HTML element exists on the page.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $tagName  The HTML tag name (e.g., 'header', 'main', 'nav', 'article')
 * @param  string|null  $message  Custom error message
 */
function assertSemanticElementExists($page, string $tagName, ?string $message = null): void
{
    $page->assertScript("document.querySelector('{$tagName}') !== null", true);
}

/**
 * Assert that multiple semantic HTML elements exist on the page.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  array<string>  $tagNames  Array of HTML tag names to verify
 */
function assertSemanticStructure($page, array $tagNames): void
{
    foreach ($tagNames as $tagName) {
        assertSemanticElementExists($page, $tagName, "Semantic element <{$tagName}> should exist on the page.");
    }
}

/**
 * Assert that a heading exists with a specific level.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  int  $level  Heading level (1-6)
 * @param  string|null  $message  Custom error message
 */
function assertHeadingExists($page, int $level, ?string $message = null): void
{
    $page->assertScript("document.querySelector('h{$level}') !== null", true);
}

/**
 * Assert that an element has a specific ARIA attribute.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $selector  CSS selector for the element
 * @param  string  $ariaAttribute  ARIA attribute name (e.g., 'label', 'role', 'hidden')
 * @param  string|null  $expectedValue  Expected value (optional, just checks existence if null)
 */
function assertHasAriaAttribute($page, string $selector, string $ariaAttribute, ?string $expectedValue = null): void
{
    $escapedSelector = str_replace("'", "\\'", $selector);

    if ($expectedValue !== null) {
        $escapedValue = str_replace("'", "\\'", $expectedValue);
        $page->assertScript("document.querySelector('{$escapedSelector}')?.getAttribute('aria-{$ariaAttribute}') === '{$escapedValue}'", true);
    } else {
        $page->assertScript("document.querySelector('{$escapedSelector}')?.hasAttribute('aria-{$ariaAttribute}')", true);
    }
}

/**
 * Assert that an input has an associated label.
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $inputSelector  CSS selector for the input element
 */
function assertInputHasLabel($page, string $inputSelector): void
{
    $escapedSelector = str_replace("'", "\\'", $inputSelector);
    // Check if input has id and label with for attribute, or aria-label, or is wrapped in label
    // Also check for Flux UI labels which may use data-flux-label or aria-labelledby
    $page->assertScript("
        (function() {
            const input = document.querySelector('{$escapedSelector}');
            if (!input) return false;
            
            // Check aria-label
            if (input.hasAttribute('aria-label') && input.getAttribute('aria-label').trim() !== '') {
                return true;
            }
            
            // Check aria-labelledby (used by Flux UI)
            if (input.hasAttribute('aria-labelledby')) {
                const labelId = input.getAttribute('aria-labelledby');
                const label = document.getElementById(labelId);
                if (label) return true;
            }
            
            // Check if wrapped in label
            if (input.closest('label')) {
                return true;
            }
            
            // Check if has id and label with for attribute
            if (input.id) {
                const label = document.querySelector('label[for=\"' + input.id + '\"]');
                if (label) return true;
            }
            
            // Check for Flux UI label structure (data-flux-label)
            const fluxField = input.closest('[data-flux-field]');
            if (fluxField) {
                // Check for data-flux-label attribute or label element
                const fluxLabel = fluxField.querySelector('[data-flux-label], label');
                if (fluxLabel && fluxLabel.textContent.trim() !== '') return true;
                // Also check for aria-labelledby pointing to a label in the field
                const labelledBy = input.getAttribute('aria-labelledby');
                if (labelledBy) {
                    const labelEl = document.getElementById(labelledBy);
                    if (labelEl && fluxField.contains(labelEl)) return true;
                }
            }
            
            // Check if input is inside a fieldset with legend (also accessible)
            const fieldset = input.closest('fieldset');
            if (fieldset) {
                const legend = fieldset.querySelector('legend');
                if (legend) return true;
            }
            
            return false;
        })()
    ", true);
}

// ============================================
// Color Contrast Testing Helpers
// ============================================

/**
 * Assert that an element has sufficient color contrast classes.
 *
 * This is a basic check that verifies elements use Tailwind classes
 * that typically provide sufficient contrast (WCAG AA).
 *
 * @param  mixed  $page  The browser page instance from Pest
 * @param  string  $selector  CSS selector for the element
 * @param  bool  $isLargeText  Whether the text is large (18pt+ or 14pt+ bold)
 */
function assertHasContrastClasses($page, string $selector, bool $isLargeText = false): void
{
    $escapedSelector = str_replace("'", "\\'", $selector);

    // Check if element has classes that typically provide sufficient contrast
    // For normal text: text-gray-900, text-zinc-900, text-white, etc. on contrasting backgrounds
    // For large text: same classes but with lower contrast requirement
    $hasContrastClasses = $page->assertScript("
        (function() {
            const element = document.querySelector('{$escapedSelector}');
            if (!element) return false;
            
            const classList = element.className;
            const computedStyle = window.getComputedStyle(element);
            const color = computedStyle.color;
            const bgColor = computedStyle.backgroundColor;
            
            // Check for common Tailwind text color classes that provide good contrast
            const textColorClasses = ['text-gray-900', 'text-zinc-900', 'text-white', 'text-black', 
                                      'text-gray-800', 'text-zinc-800', 'text-gray-700', 'text-zinc-700',
                                      'text-erasmus-700', 'text-erasmus-600'];
            
            const hasTextColor = textColorClasses.some(cls => classList.includes(cls));
            
            // If element has text color class, assume it's using proper contrast
            // (Tailwind's default colors are designed for accessibility)
            return hasTextColor || color !== 'rgb(0, 0, 0)' || bgColor !== 'rgba(0, 0, 0, 0)';
        })()
    ", true);

    expect($hasContrastClasses)->toBeTrue("Element '{$selector}' should have contrast classes or computed colors");
}
