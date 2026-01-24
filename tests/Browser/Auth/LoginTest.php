<?php

use App\Models\User;

use function Tests\Browser\Helpers\createAuthTestUser;
use function Tests\Browser\Helpers\performLogin;

// ============================================
// Test: Verificar formulario de login
// ============================================

it('displays the login form with all required elements', function () {
    $page = visit(route('login'));

    $page->assertSee('Log in to your account')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]')
        ->assertSeeLink('Forgot your password?')
        ->assertSee('Sign up')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Login con credenciales válidas
// ============================================

it('logs in successfully with valid credentials', function () {
    $user = createAuthTestUser(['email' => 'test@example.com']);

    $page = performLogin($user);

    $page->assertPathIs('/dashboard')
        ->assertSee('Dashboard')
        ->assertNoJavascriptErrors();

    $this->assertAuthenticated();
});

// ============================================
// Test: Login con credenciales inválidas
// ============================================

it('shows error with incorrect email', function () {
    User::factory()->withoutTwoFactor()->create(['email' => 'good@example.com']);

    $page = visit(route('login'))
        ->fill('email', 'wrong@example.com')
        ->fill('password', 'password')
        ->click('Log in');

    $page->assertPathIs('/login')
        ->assertSee('These credentials do not match our records');

    $this->assertGuest();
});

it('shows error with incorrect password', function () {
    $user = createAuthTestUser(['email' => 'u@ex.com']);

    $page = visit(route('login'))
        ->fill('email', 'u@ex.com')
        ->fill('password', 'wrong')
        ->click('Log in');

    $page->assertPathIs('/login')
        ->assertSee('These credentials do not match our records');

    $this->assertGuest();
});

// ============================================
// Test: Validación
// ============================================

it('shows validation error when email is invalid format', function () {
    $page = visit(route('login'))
        ->fill('email', 'invalid')
        ->fill('password', 'password')
        ->click('Log in');

    $page->assertPathIs('/login');
    $this->assertGuest();
});

it('stays on login when password is empty', function () {
    $page = visit(route('login'))
        ->fill('email', 'a@b.com')
        ->click('Log in');

    $page->assertPathIs('/login');
    $this->assertGuest();
});

// ============================================
// Test: Redirección a URL intentada
// ============================================

it('redirects to intended url after login when visiting protected route first', function () {
    $user = createAuthTestUser(['email' => 'intended@test.com']);

    $page = visit(route('admin.dashboard'));

    $page->assertPathIs('/login');

    $page->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Log in');

    $page->assertPathIs('/admin');
});

// ============================================
// Test: Opción Remember me
// ============================================

it('displays remember me checkbox and can log in', function () {
    $user = createAuthTestUser(['email' => 'remember@test.com']);

    $page = visit(route('login'));

    $page->assertSee('Remember me');

    $page->fill('email', $user->email)
        ->fill('password', 'password')
        ->click('Log in');

    $page->assertPathIs('/dashboard')
        ->assertSee('Dashboard');

    $this->assertAuthenticated();
});

// ============================================
// Test: Navegación desde login
// ============================================

it('navigates to forgot password page from login', function () {
    $page = visit(route('login'))
        ->click('Forgot your password?');

    $page->assertPathIs('/forgot-password');
});

it('navigates to register page from login', function () {
    $page = visit(route('login'))
        ->click('Sign up');

    $page->assertPathIs('/register');
});

// ============================================
// Test: Sin errores de JavaScript
// ============================================

it('has no javascript errors on login page', function () {
    $page = visit(route('login'));

    $page->assertNoJavascriptErrors();
});
