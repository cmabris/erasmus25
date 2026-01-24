<?php

use App\Support\Roles;

use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;

$adminRoutes = [
    'admin.dashboard' => '/admin',
    'admin.programs.index' => '/admin/programas',
    'admin.news.index' => '/admin/noticias',
    'admin.users.index' => '/admin/usuarios',
    'admin.roles.index' => '/admin/roles',
];

// ============================================
// Test: Usuario no autenticado es redirigido a login
// ============================================

it('redirects unauthenticated user to login when visiting admin routes', function () use ($adminRoutes) {
    foreach ($adminRoutes as $routeName => $expectedPath) {
        $page = visit(route($routeName));
        $page->assertPathIs('/login');
    }
});

// ============================================
// Test: Usuario autenticado sin permisos recibe 403 en users y roles
// ============================================

it('returns 403 for viewer on admin users index', function () {
    $viewer = createAuthTestUser(['email' => 'viewer403@test.com'], Roles::VIEWER);

    $page = performLogin($viewer)
        ->navigate(route('admin.users.index'));

    $page->assertSee('403');
});

it('returns 403 for viewer on admin roles index', function () {
    $viewer = createAuthTestUser(['email' => 'viewer403roles@test.com'], Roles::VIEWER);

    $page = performLogin($viewer)
        ->navigate(route('admin.roles.index'));

    $page->assertSee('403');
});

// ============================================
// Test: Usuario con rol viewer puede acceder al dashboard y listados de solo lectura
// ============================================

it('allows viewer to access admin dashboard', function () {
    $viewer = createAuthTestUser(['email' => 'viewerdash@test.com'], Roles::VIEWER);

    $page = performLogin($viewer)
        ->navigate(route('admin.dashboard'));

    $page->assertPathIs('/admin')
        ->assertSee('Dashboard');
});

it('allows viewer to access admin programs and news index', function () {
    $viewer = createAuthTestUser(['email' => 'viewerlist@test.com'], Roles::VIEWER);

    $page = performLogin($viewer);

    $page->navigate(route('admin.programs.index'))
        ->assertPathIs('/admin/programas')
        ->assertSee('Programas');

    $page->navigate(route('admin.news.index'))
        ->assertPathIs('/admin/noticias')
        ->assertSee('Noticias');
});

// ============================================
// Test: Usuario admin puede acceder a programas, noticias y usuarios
// ============================================

it('allows admin to access programs news and users', function () {
    $admin = createAuthTestUser(['email' => 'admin@test.com'], Roles::ADMIN);

    $page = performLogin($admin);

    $page->navigate(route('admin.programs.index'))
        ->assertPathIs('/admin/programas');

    $page->navigate(route('admin.news.index'))
        ->assertPathIs('/admin/noticias');

    $page->navigate(route('admin.users.index'))
        ->assertPathIs('/admin/usuarios');
});

// ============================================
// Test: Usuario super-admin puede acceder a todas las rutas
// ============================================

it('allows super-admin to access all admin routes', function () use ($adminRoutes) {
    $superAdmin = createAuthTestUser(['email' => 'super@test.com'], Roles::SUPER_ADMIN);

    $page = performLogin($superAdmin);

    foreach ($adminRoutes as $routeName => $path) {
        $page->navigate(route($routeName))
            ->assertPathIs($path);
    }
});

// ============================================
// Test: Usuario sin email verificado (omitido: User no implementa MustVerifyEmail)
// ============================================

// it('redirects unverified user to email verification page when visiting admin', ...);
// Omitido: User no implementa MustVerifyEmail; el middleware 'verified' no redirige.

// ============================================
// Test: Logout y acceso de nuevo a admin
// ============================================

it('redirects to login after logout when visiting admin', function () {
    $user = createAuthTestUser(['name' => 'Logout Test', 'email' => 'logout@test.com'], Roles::VIEWER);

    $page = performLogin($user)
        ->navigate(route('admin.dashboard'));

    $page->assertPathIs('/admin');

    $page->click('Logout Test')
        ->click('Log Out');

    $page->navigate(route('admin.dashboard'))
        ->assertPathIs('/login');
});
