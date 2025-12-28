<?php

use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('NewsletterSubscription Scopes', function () {
    beforeEach(function () {
        // Create subscriptions with different states
        // Explicitly set programs to null to avoid interference with program-specific tests
        NewsletterSubscription::factory()->create([
            'email' => 'active1@example.com',
            'is_active' => true,
            'verified_at' => now()->subDays(5),
            'programs' => null,
        ]);

        NewsletterSubscription::factory()->create([
            'email' => 'active2@example.com',
            'is_active' => true,
            'verified_at' => now()->subDays(10),
            'programs' => null,
        ]);

        NewsletterSubscription::factory()->create([
            'email' => 'inactive1@example.com',
            'is_active' => false,
            'verified_at' => now()->subDays(5),
            'unsubscribed_at' => now()->subDays(2),
            'programs' => null,
        ]);

        NewsletterSubscription::factory()->unverified()->create([
            'email' => 'unverified1@example.com',
            'is_active' => false,
            'programs' => null,
        ]);

        NewsletterSubscription::factory()->unverified()->create([
            'email' => 'unverified2@example.com',
            'is_active' => false,
            'programs' => null,
        ]);
    });

    it('filters active subscriptions', function () {
        $active = NewsletterSubscription::active()->get();

        expect($active)->toHaveCount(2)
            ->and($active->pluck('email')->toArray())->toContain('active1@example.com')
            ->and($active->pluck('email')->toArray())->toContain('active2@example.com')
            ->and($active->pluck('email')->toArray())->not->toContain('inactive1@example.com');
    });

    it('filters verified subscriptions', function () {
        $verified = NewsletterSubscription::verified()->get();

        expect($verified)->toHaveCount(3)
            ->and($verified->pluck('email')->toArray())->toContain('active1@example.com')
            ->and($verified->pluck('email')->toArray())->toContain('active2@example.com')
            ->and($verified->pluck('email')->toArray())->toContain('inactive1@example.com')
            ->and($verified->pluck('email')->toArray())->not->toContain('unverified1@example.com');
    });

    it('filters unverified subscriptions', function () {
        $unverified = NewsletterSubscription::unverified()->get();

        expect($unverified)->toHaveCount(2)
            ->and($unverified->pluck('email')->toArray())->toContain('unverified1@example.com')
            ->and($unverified->pluck('email')->toArray())->toContain('unverified2@example.com')
            ->and($unverified->pluck('email')->toArray())->not->toContain('active1@example.com');
    });

    it('filters subscriptions for specific program', function () {
        // Clear existing subscriptions first
        NewsletterSubscription::query()->delete();

        NewsletterSubscription::factory()->create([
            'email' => 'program1@example.com',
            'programs' => ['KA1xx', 'KA121-VET'],
            'is_active' => true,
        ]);

        NewsletterSubscription::factory()->create([
            'email' => 'program2@example.com',
            'programs' => ['KA131-HED'],
            'is_active' => true,
        ]);

        NewsletterSubscription::factory()->create([
            'email' => 'noprogram@example.com',
            'programs' => null,
            'is_active' => true,
        ]);

        $forProgram = NewsletterSubscription::forProgram('KA1xx')->get();

        expect($forProgram)->toHaveCount(1)
            ->and($forProgram->first()->email)->toBe('program1@example.com');
    });

    it('filters verified subscriptions for specific program', function () {
        // Clear existing subscriptions to avoid interference from beforeEach
        NewsletterSubscription::query()->delete();

        NewsletterSubscription::factory()->create([
            'email' => 'verified-program@example.com',
            'programs' => ['KA1xx'],
            'is_active' => true,
            'verified_at' => now(),
        ]);

        NewsletterSubscription::factory()->unverified()->create([
            'email' => 'unverified-program@example.com',
            'programs' => ['KA1xx'],
            'is_active' => false,
        ]);

        $verifiedForProgram = NewsletterSubscription::verifiedForProgram('KA1xx')->get();

        expect($verifiedForProgram)->toHaveCount(1)
            ->and($verifiedForProgram->first()->email)->toBe('verified-program@example.com');
    });
});

describe('NewsletterSubscription Helper Methods', function () {
    it('checks if subscription is verified', function () {
        $verified = NewsletterSubscription::factory()->create([
            'verified_at' => now(),
        ]);

        $unverified = NewsletterSubscription::factory()->unverified()->create();

        expect($verified->isVerified())->toBeTrue()
            ->and($unverified->isVerified())->toBeFalse();
    });

    it('checks if subscription is active', function () {
        $active = NewsletterSubscription::factory()->create([
            'is_active' => true,
        ]);

        $inactive = NewsletterSubscription::factory()->create([
            'is_active' => false,
        ]);

        expect($active->isActive())->toBeTrue()
            ->and($inactive->isActive())->toBeFalse();
    });

    it('verifies a subscription', function () {
        $subscription = NewsletterSubscription::factory()->unverified()->create([
            'is_active' => false,
            'verified_at' => null,
        ]);

        $result = $subscription->verify();

        expect($result)->toBeTrue()
            ->and($subscription->fresh()->is_active)->toBeTrue()
            ->and($subscription->fresh()->verified_at)->not->toBeNull();
    });

    it('unsubscribes a subscription', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'is_active' => true,
            'unsubscribed_at' => null,
        ]);

        $result = $subscription->unsubscribe();

        expect($result)->toBeTrue()
            ->and($subscription->fresh()->is_active)->toBeFalse()
            ->and($subscription->fresh()->unsubscribed_at)->not->toBeNull();
    });

    it('generates verification token', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'verification_token' => null,
        ]);

        $token = $subscription->generateVerificationToken();

        expect($token)->toBeString()
            ->and(strlen($token))->toBe(32)
            ->and($subscription->fresh()->verification_token)->toBe($token);
    });

    it('checks if subscription has specific program', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA1xx', 'KA121-VET'],
        ]);

        expect($subscription->hasProgram('KA1xx'))->toBeTrue()
            ->and($subscription->hasProgram('KA121-VET'))->toBeTrue()
            ->and($subscription->hasProgram('KA131-HED'))->toBeFalse();
    });

    it('returns false for hasProgram when programs is null', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => null,
        ]);

        expect($subscription->hasProgram('KA1xx'))->toBeFalse();
    });

    it('returns false for hasProgram when programs is empty array', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => [],
        ]);

        expect($subscription->hasProgram('KA1xx'))->toBeFalse();
    });
});
