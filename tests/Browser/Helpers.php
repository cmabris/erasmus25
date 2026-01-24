<?php

namespace Tests\Browser\Helpers;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\CallPhase;
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
