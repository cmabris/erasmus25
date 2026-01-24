<?php

use App\Models\User;

// ============================================
// Test: Verificar formulario de registro
// ============================================

it('displays the register form with all required elements', function () {
    $page = visit(route('register'));

    $page->assertSee('Create an account')
        ->assertSee('Name')
        ->assertSee('Email address')
        ->assertSee('Password')
        ->assertSee('Confirm password')
        ->assertPresent('input[name="name"]')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]')
        ->assertPresent('input[name="password_confirmation"]')
        ->assertSee('Log in')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Registro con datos válidos
// ============================================

it('registers successfully with valid data', function () {
    $page = visit(route('register'))
        ->fill('name', 'Foo Bar')
        ->fill('email', 'new@example.com')
        ->fill('password', 'SecurePass123!')
        ->fill('password_confirmation', 'SecurePass123!')
        ->click('Create account');

    $this->assertDatabaseHas('users', ['email' => 'new@example.com']);

    $page->assertPathIs('/dashboard')
        ->assertSee('Dashboard');

    $this->assertAuthenticated();
});

// ============================================
// Test: Validación – email duplicado
// ============================================

it('shows validation error when email already exists', function () {
    User::factory()->create(['email' => 'exists@example.com']);

    $countBefore = User::count();

    $page = visit(route('register'))
        ->fill('name', 'Foo Bar')
        ->fill('email', 'exists@example.com')
        ->fill('password', 'SecurePass123!')
        ->fill('password_confirmation', 'SecurePass123!')
        ->click('Create account');

    expect(User::count())->toBe($countBefore);

    $page->assertPathIs('/register');
});

// ============================================
// Test: Validación – contraseña y confirmación no coinciden
// ============================================

it('shows validation error when password and confirmation do not match', function () {
    $countBefore = User::count();

    $page = visit(route('register'))
        ->fill('name', 'Foo Bar')
        ->fill('email', 'noconfirm@example.com')
        ->fill('password', 'SecurePass123!')
        ->fill('password_confirmation', 'OtherPass456!')
        ->click('Create account');

    expect(User::count())->toBe($countBefore);

    $page->assertPathIs('/register');
});

// ============================================
// Test: Validación – campos requeridos / formato
// ============================================

it('shows validation error when email format is invalid', function () {
    $page = visit(route('register'))
        ->fill('name', 'Foo Bar')
        ->fill('email', 'not-an-email')
        ->fill('password', 'SecurePass123!')
        ->fill('password_confirmation', 'SecurePass123!')
        ->click('Create account');

    $page->assertPathIs('/register');
    $this->assertGuest();
});

it('shows validation error when password is too short', function () {
    $page = visit(route('register'))
        ->fill('name', 'Foo Bar')
        ->fill('email', 'shortpw@example.com')
        ->fill('password', 'Ab1')
        ->fill('password_confirmation', 'Ab1')
        ->click('Create account');

    $page->assertPathIs('/register');
    $this->assertGuest();
});

// ============================================
// Test: Navegación desde registro a login
// ============================================

it('navigates to login page from register', function () {
    $page = visit(route('register'))
        ->click('Log in');

    $page->assertPathIs('/login');
});

// ============================================
// Test: Sin errores de JavaScript
// ============================================

it('has no javascript errors on register page', function () {
    $page = visit(route('register'));

    $page->assertNoJavascriptErrors();
});
