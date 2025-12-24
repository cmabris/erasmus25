<?php

use App\Livewire\Public\Newsletter\Unsubscribe;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Newsletter Unsubscribe Component - Unsubscribe by Token', function () {
    it('unsubscribes successfully with valid token', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
            'verification_token' => $token = Str::random(32),
            'verified_at' => now()->subDays(10),
            'unsubscribed_at' => null,
        ]);

        Livewire::test(Unsubscribe::class, ['token' => $token])
            ->assertSet('status', 'success')
            ->assertSet('subscription.id', $subscription->id);

        $subscription->refresh();
        expect($subscription->is_active)->toBeFalse()
            ->and($subscription->unsubscribed_at)->not->toBeNull();
    });

    it('shows success message after unsubscription', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verification_token' => $token = Str::random(32),
            'is_active' => true,
        ]);

        Livewire::test(Unsubscribe::class, ['token' => $token])
            ->assertSet('status', 'success')
            ->assertSee(__('Suscripción cancelada'));
    });

    it('displays subscription details after unsubscription', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'verification_token' => $token = Str::random(32),
            'is_active' => true,
        ]);

        Livewire::test(Unsubscribe::class, ['token' => $token])
            ->assertSee('test@example.com')
            ->assertSee('John Doe');
    });
});

describe('Newsletter Unsubscribe Component - Already Unsubscribed', function () {
    it('shows message when already unsubscribed', function () {
        $subscription = NewsletterSubscription::factory()->unsubscribed()->create([
            'verification_token' => $token = Str::random(32),
            'is_active' => false,
            'unsubscribed_at' => now()->subDays(5),
        ]);

        Livewire::test(Unsubscribe::class, ['token' => $token])
            ->assertSet('status', 'already_unsubscribed')
            ->assertSee(__('Suscripción ya cancelada'));
    });

    it('does not change subscription when already unsubscribed', function () {
        $subscription = NewsletterSubscription::factory()->unsubscribed()->create([
            'verification_token' => $token = Str::random(32),
            'is_active' => false,
            'unsubscribed_at' => $originalUnsubscribedAt = now()->subDays(5),
        ]);

        Livewire::test(Unsubscribe::class, ['token' => $token]);

        $subscription->refresh();
        expect($subscription->unsubscribed_at->timestamp)->toBe($originalUnsubscribedAt->timestamp)
            ->and($subscription->is_active)->toBeFalse();
    });
});

describe('Newsletter Unsubscribe Component - Invalid Token', function () {
    it('shows error for invalid token', function () {
        Livewire::test(Unsubscribe::class, ['token' => 'invalid-token-12345'])
            ->assertSet('status', 'not_found')
            ->assertSee(__('Suscripción no encontrada'));
    });

    it('shows error for non-existent token', function () {
        Livewire::test(Unsubscribe::class, ['token' => Str::random(32)])
            ->assertSet('status', 'not_found')
            ->assertSee(__('No se encontró ninguna suscripción con este token.'));
    });
});

describe('Newsletter Unsubscribe Component - Unsubscribe by Email', function () {
    it('renders unsubscribe form when no token provided', function () {
        Livewire::test(Unsubscribe::class)
            ->assertSee(__('Cancelar tu suscripción'))
            ->assertSee(__('Correo electrónico'));
    });

    it('validates email is required', function () {
        Livewire::test(Unsubscribe::class)
            ->set('email', '')
            ->call('unsubscribeByEmail')
            ->assertHasErrors(['email']);
    });

    it('validates email format', function () {
        Livewire::test(Unsubscribe::class)
            ->set('email', 'invalid-email')
            ->call('unsubscribeByEmail')
            ->assertHasErrors(['email']);
    });

    it('successfully unsubscribes by email', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        Livewire::test(Unsubscribe::class)
            ->set('email', 'test@example.com')
            ->call('unsubscribeByEmail')
            ->assertSet('status', 'success');

        $subscription->refresh();
        expect($subscription->is_active)->toBeFalse()
            ->and($subscription->unsubscribed_at)->not->toBeNull();
    });

    it('shows error when email not found', function () {
        Livewire::test(Unsubscribe::class)
            ->set('email', 'nonexistent@example.com')
            ->call('unsubscribeByEmail')
            ->assertSet('status', 'not_found')
            ->assertSee(__('No se encontró ninguna suscripción con este correo electrónico.'));
    });

    it('shows error when email already unsubscribed', function () {
        $subscription = NewsletterSubscription::factory()->unsubscribed()->create([
            'email' => 'test@example.com',
            'is_active' => false,
        ]);

        Livewire::test(Unsubscribe::class)
            ->set('email', 'test@example.com')
            ->call('unsubscribeByEmail')
            ->assertSet('status', 'already_unsubscribed');
    });

    it('stores email in lowercase when unsubscribing', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true,
        ]);

        Livewire::test(Unsubscribe::class)
            ->set('email', 'TEST@EXAMPLE.COM')
            ->call('unsubscribeByEmail')
            ->assertSet('status', 'success');
    });
});

describe('Newsletter Unsubscribe Component - Rendering', function () {
    it('renders the unsubscribe page', function () {
        $this->get(route('newsletter.unsubscribe'))
            ->assertOk()
            ->assertSeeLivewire(Unsubscribe::class);
    });

    it('renders unsubscribe page with token', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verification_token' => $token = Str::random(32),
            'is_active' => true,
        ]);

        $this->get(route('newsletter.unsubscribe.token', $token))
            ->assertOk()
            ->assertSeeLivewire(Unsubscribe::class);
    });

    it('shows warning message in form', function () {
        Livewire::test(Unsubscribe::class)
            ->assertSee(__('¿Estás seguro?'))
            ->assertSee(__('Al cancelar tu suscripción'));
    });
});

