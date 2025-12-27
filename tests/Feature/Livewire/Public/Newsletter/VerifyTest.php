<?php

use App\Livewire\Public\Newsletter\Verify;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
});

describe('Newsletter Verify Component - Successful Verification', function () {
    it('verifies subscription with valid token', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'email' => 'test@example.com',
            'is_active' => false,
            'verification_token' => $token = Str::random(32),
            'verified_at' => null,
        ]);

        Livewire::test(Verify::class, ['token' => $token])
            ->assertSet('status', 'success')
            ->assertSet('subscription.id', $subscription->id);

        $subscription->refresh();
        expect($subscription->is_active)->toBeTrue()
            ->and($subscription->verified_at)->not->toBeNull();
    });

    it('activates subscription when verified', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'verification_token' => $token = Str::random(32),
            'is_active' => false,
        ]);

        Livewire::test(Verify::class, ['token' => $token]);

        $subscription->refresh();
        expect($subscription->is_active)->toBeTrue()
            ->and($subscription->verified_at)->not->toBeNull();
    });

    it('shows success message after verification', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'verification_token' => $token = Str::random(32),
        ]);

        Livewire::test(Verify::class, ['token' => $token])
            ->assertSet('status', 'success')
            ->assertSee(__('¡Verificación exitosa!'));
    });

    it('displays subscription details after verification', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'programs' => ['KA1xx', 'KA121-VET'],
            'verification_token' => $token = Str::random(32),
        ]);

        Livewire::test(Verify::class, ['token' => $token])
            ->assertSee('test@example.com')
            ->assertSee('John Doe')
            ->assertSee('KA1xx')
            ->assertSee('KA121-VET');
    });
});

describe('Newsletter Verify Component - Already Verified', function () {
    it('shows message when subscription is already verified', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verification_token' => $token = Str::random(32),
            'verified_at' => now()->subDays(5),
            'is_active' => true,
        ]);

        Livewire::test(Verify::class, ['token' => $token])
            ->assertSet('status', 'already_verified')
            ->assertSee(__('Suscripción ya verificada'));
    });

    it('does not change subscription when already verified', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verification_token' => $token = Str::random(32),
            'verified_at' => $originalVerifiedAt = now()->subDays(5),
            'is_active' => true,
        ]);

        Livewire::test(Verify::class, ['token' => $token]);

        $subscription->refresh();
        expect($subscription->verified_at->timestamp)->toBe($originalVerifiedAt->timestamp)
            ->and($subscription->is_active)->toBeTrue();
    });
});

describe('Newsletter Verify Component - Invalid Token', function () {
    it('shows error for invalid token', function () {
        Livewire::test(Verify::class, ['token' => 'invalid-token-12345'])
            ->assertSet('status', 'invalid')
            ->assertSee(__('Token inválido'));
    });

    it('shows error for non-existent token', function () {
        Livewire::test(Verify::class, ['token' => Str::random(32)])
            ->assertSet('status', 'invalid')
            ->assertSee(__('El token de verificación no es válido o ha expirado.'));
    });

    it('does not create subscription for invalid token', function () {
        $countBefore = NewsletterSubscription::count();

        Livewire::test(Verify::class, ['token' => 'invalid-token']);

        expect(NewsletterSubscription::count())->toBe($countBefore);
    });
});

describe('Newsletter Verify Component - Rendering', function () {
    it('renders the verify page', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'verification_token' => $token = Str::random(32),
        ]);

        $this->get(route('newsletter.verify', $token))
            ->assertOk()
            ->assertSeeLivewire(Verify::class);
    });

    it('shows correct page title', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'verification_token' => $token = Str::random(32),
        ]);

        $this->get(route('newsletter.verify', $token))
            ->assertSee(__('Verificación de Suscripción'));
    });
});
