<?php

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Tests\Browser\Helpers\createAuthTestUser;

// ============================================
// Test: Verificar formulario "Forgot password"
// ============================================

it('displays the forgot password form with all required elements', function () {
    $page = visit(route('password.request'));

    $page->assertSee('Forgot password')
        ->assertSee('Enter your email to receive a password reset link')
        ->assertPresent('input[name="email"]')
        ->assertSee('Email password reset link')
        ->assertSee('log in')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Solicitud de enlace con email existente
// ============================================

it('sends password reset link when email exists', function () {
    Notification::fake();

    $user = createAuthTestUser(['email' => 'u@example.com']);

    $page = visit(route('password.request'))
        ->fill('email', 'u@example.com')
        ->click('Email password reset link');

    $page->assertSee('We have emailed your password reset link.');

    Notification::assertSentTo($user, ResetPassword::class);
});

// ============================================
// Test: Solicitud con email inexistente
// ============================================

it('stays on forgot password when email does not exist', function () {
    $page = visit(route('password.request'))
        ->fill('email', 'nonexistent@example.com')
        ->click('Email password reset link');

    $page->assertPathIs('/forgot-password');
});

// ============================================
// Test: Formulario de reset con token válido
// ============================================

it('displays reset password form with valid token', function () {
    $user = User::factory()->create(['email' => 'resetform@example.com']);
    $token = Password::broker(config('fortify.passwords'))->createToken($user);
    $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

    $page = visit($url);

    $page->assertSee('Reset password')
        ->assertSee('Please enter your new password below')
        ->assertPresent('input[name="email"]')
        ->assertPresent('input[name="password"]')
        ->assertPresent('input[name="password_confirmation"]')
        ->assertPresent('button[data-test="reset-password-button"]')
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Cambio de contraseña exitoso
// ============================================

it('resets password successfully and user can log in with new password', function () {
    Notification::fake();

    $user = createAuthTestUser(['email' => 'resetok@example.com']);

    visit(route('password.request'))
        ->fill('email', 'resetok@example.com')
        ->click('Email password reset link');

    $token = null;
    Notification::assertSentTo($user, ResetPassword::class, function ($n) use (&$token) {
        $token = $n->token;

        return true;
    });

    $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

    $page = visit($url)
        ->fill('email', $user->email)
        ->fill('password', 'password')
        ->fill('password_confirmation', 'password')
        ->submit();

    $page->assertPathIs('/login');

    $page = visit(route('login'))
        ->fill('email', 'resetok@example.com')
        ->fill('password', 'password')
        ->click('Log in');

    $page->assertPathIs('/dashboard');
    $this->assertAuthenticated();
});

// ============================================
// Test: Token inválido o expirado
// ============================================

it('does not change password when token is invalid', function () {
    $user = createAuthTestUser(['email' => 'u@example.com']);

    $page = visit(route('password.reset', ['token' => 'invalid', 'email' => $user->email]))
        ->fill('password', 'NewSecure123!')
        ->fill('password_confirmation', 'NewSecure123!')
        ->click('Reset password');

    $page->assertSee('Reset password');

    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

// ============================================
// Test: Validación en reset – contraseña y confirmación no coinciden
// ============================================

it('shows validation error when password and confirmation do not match on reset', function () {
    $user = User::factory()->create(['email' => 'nomatch@example.com']);
    $token = Password::broker(config('fortify.passwords'))->createToken($user);
    $url = route('password.reset', ['token' => $token, 'email' => $user->email]);

    $page = visit($url)
        ->fill('password', 'NewSecure123!')
        ->fill('password_confirmation', 'OtherPass456!')
        ->click('Reset password');

    $page->assertSee('Reset password');
});

// ============================================
// Test: Navegación desde "Forgot" a login
// ============================================

it('navigates to login from forgot password page', function () {
    $page = visit(route('password.request'))
        ->click('log in');

    $page->assertPathIs('/login');
});

// ============================================
// Test: Sin errores de JavaScript
// ============================================

it('has no javascript errors on forgot password page', function () {
    $page = visit(route('password.request'));

    $page->assertNoJavascriptErrors();
});
