<?php

use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscription;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

use function Tests\Browser\Helpers\createNewsletterTestData;

beforeEach(function () {
    App::setLocale('es');
    createNewsletterTestData();
});

// ============================================
// Test: Verificar formulario de suscripción
// ============================================

it('displays the newsletter subscription form with all required elements', function () {
    $page = visit(route('newsletter.subscribe'));

    $page->assertSee(__('common.newsletter.stay_informed'))
        ->assertSee(__('common.newsletter.email'))
        ->assertSee(__('common.newsletter.subscribe'))
        ->assertPresent('input[name="email"]')
        ->assertSee('Programa KA1')
        ->assertSee('Programa KA2')
        ->assertSee('KA1')
        ->assertSee(__('common.newsletter.accept_privacy'))
        ->assertNoJavascriptErrors();
});

// ============================================
// Test: Validación de email — campo vacío
// ============================================

it('shows validation error when email is empty', function () {
    $page = visit(route('newsletter.subscribe'));
    $page->script("document.querySelector('input[name=email]').removeAttribute('required')");
    $page->check('acceptPrivacy')
        ->click(__('common.newsletter.subscribe'))
        ->wait(1);

    $page->assertSee('obligatorio');
});

// ============================================
// Test: Validación de email — formato inválido
// ============================================

it('shows validation error when email format is invalid', function () {
    $page = visit(route('newsletter.subscribe'));
    $page->script("document.querySelector('input[name=email]').setAttribute('type','text')");
    $page->fill('email', 'invalid')
        ->check('acceptPrivacy')
        ->click(__('common.newsletter.subscribe'))
        ->wait(1);

    $page->assertSee('válida');
});

// ============================================
// Test: Validación de email — duplicado
// ============================================

it('shows validation error when email is already subscribed', function () {
    NewsletterSubscription::factory()->create(['email' => 'existente@example.com']);

    $page = visit(route('newsletter.subscribe'))
        ->fill('email', 'existente@example.com')
        ->check('acceptPrivacy')
        ->click(__('common.newsletter.subscribe'))
        ->wait(1);

    $page->assertDontSee(__('common.newsletter.subscription_success'))
        ->assertSee(__('common.newsletter.email'));

    $this->assertDatabaseCount('newsletter_subscriptions', 1);
});

// ============================================
// Test: Validación de aceptación de privacidad
// ============================================

it('shows validation error when privacy policy is not accepted', function () {
    $page = visit(route('newsletter.subscribe'))
        ->fill('email', 'nuevo@example.com')
        ->click(__('common.newsletter.subscribe'));

    $page->assertSee('Debe aceptar la política de privacidad');
});

// ============================================
// Test: Selección de programas de interés
// ============================================

it('creates subscription with selected programs when program is chosen', function () {
    Mail::fake();

    $page = visit(route('newsletter.subscribe'))
        ->fill('email', 'test@example.com')
        ->check('acceptPrivacy')
        ->click('Programa KA1')
        ->click(__('common.newsletter.subscribe'));

    $page->assertSee(__('common.newsletter.subscription_success'));

    $subscription = NewsletterSubscription::where('email', 'test@example.com')->first();
    expect($subscription)->not->toBeNull()
        ->and($subscription->programs)->toContain('KA1');
});

// ============================================
// Test: Envío exitoso y confirmación
// ============================================

it('subscribes successfully and sends verification email', function () {
    Mail::fake();

    $page = visit(route('newsletter.subscribe'))
        ->fill('email', 'nuevo@example.com')
        ->check('acceptPrivacy')
        ->click(__('common.newsletter.subscribe'));

    $page->assertSee(__('common.newsletter.subscription_success'))
        ->assertSee(__('common.newsletter.verification_email_sent'))
        ->assertNoJavascriptErrors();

    Mail::assertSent(NewsletterVerificationMail::class);
    $this->assertDatabaseHas('newsletter_subscriptions', ['email' => 'nuevo@example.com']);
});

// ============================================
// Test: Manejo de errores — no se muestra éxito si hay error de validación
// ============================================

it('does not show success message when validation fails', function () {
    NewsletterSubscription::factory()->create(['email' => 'duplicado@example.com']);

    $page = visit(route('newsletter.subscribe'))
        ->fill('email', 'duplicado@example.com')
        ->check('acceptPrivacy')
        ->click(__('common.newsletter.subscribe'));

    $page->assertDontSee(__('common.newsletter.subscription_success'))
        ->assertSee('Correo electrónico');
});

// ============================================
// Test: Sin errores de JavaScript en la página de suscripción
// ============================================

it('has no javascript errors on newsletter subscribe page', function () {
    $page = visit(route('newsletter.subscribe'));

    $page->assertNoJavascriptErrors();
});
