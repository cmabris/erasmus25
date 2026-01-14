<?php

use App\Models\NewsletterSubscription;
use App\Models\Program;

it('stores programs as json array', function () {
    $subscription = NewsletterSubscription::factory()->create([
        'programs' => ['KA1xx', 'KA121-VET', 'KA131-HED'],
    ]);

    expect($subscription->programs)->toBeArray()
        ->and($subscription->programs)->toHaveCount(3)
        ->and($subscription->programs[0])->toBe('KA1xx');
});

it('can have null programs', function () {
    $subscription = NewsletterSubscription::factory()->create([
        'programs' => null,
    ]);

    expect($subscription->programs)->toBeNull();
});

it('can have empty programs array', function () {
    $subscription = NewsletterSubscription::factory()->create([
        'programs' => [],
    ]);

    expect($subscription->programs)->toBeArray()
        ->and($subscription->programs)->toHaveCount(0);
});

it('can subscribe and unsubscribe', function () {
    $subscription = NewsletterSubscription::factory()->create([
        'is_active' => true,
        'subscribed_at' => now(),
        'unsubscribed_at' => null,
    ]);

    expect($subscription->is_active)->toBeTrue()
        ->and($subscription->unsubscribed_at)->toBeNull();

    $subscription->update([
        'is_active' => false,
        'unsubscribed_at' => now(),
    ]);

    expect($subscription->is_active)->toBeFalse()
        ->and($subscription->unsubscribed_at)->not->toBeNull();
});

describe('NewsletterSubscription program helpers', function () {
    it('returns empty collection when programs is null', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => null,
        ]);

        expect($subscription->programs_models)->toBeEmpty()
            ->and($subscription->programs_display)->toBe('-')
            ->and($subscription->programs_codes)->toBe('-');
    });

    it('returns empty collection when programs is empty array', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => [],
        ]);

        expect($subscription->programs_models)->toBeEmpty()
            ->and($subscription->programs_display)->toBe('-')
            ->and($subscription->programs_codes)->toBe('-');
    });

    it('returns program models when programs exist', function () {
        $program1 = Program::factory()->create([
            'code' => 'KA1xx',
            'name' => 'Programa KA1',
        ]);
        $program2 = Program::factory()->create([
            'code' => 'KA121-VET',
            'name' => 'Programa KA121',
        ]);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA1xx', 'KA121-VET'],
        ]);

        $programs = $subscription->programs_models;

        expect($programs)->toHaveCount(2)
            ->and($programs->pluck('code')->toArray())->toContain('KA1xx', 'KA121-VET')
            ->and($subscription->programs_display)->toContain('Programa KA1')
            ->and($subscription->programs_display)->toContain('Programa KA121')
            ->and($subscription->programs_codes)->toBe('KA1xx, KA121-VET');
    });

    it('returns program codes when programs do not exist in database', function () {
        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['INVALID-CODE', 'ANOTHER-INVALID'],
        ]);

        expect($subscription->programs_models)->toBeEmpty()
            ->and($subscription->programs_display)->toBe('INVALID-CODE, ANOTHER-INVALID')
            ->and($subscription->programs_codes)->toBe('INVALID-CODE, ANOTHER-INVALID');
    });

    it('returns mixed display when some programs exist and others do not', function () {
        $program = Program::factory()->create([
            'code' => 'KA1xx',
            'name' => 'Programa KA1',
        ]);

        $subscription = NewsletterSubscription::factory()->create([
            'programs' => ['KA1xx', 'INVALID-CODE'],
        ]);

        expect($subscription->programs_models)->toHaveCount(1)
            ->and($subscription->programs_display)->toContain('Programa KA1')
            ->and($subscription->programs_display)->toContain('INVALID-CODE')
            ->and($subscription->programs_codes)->toBe('KA1xx, INVALID-CODE');
    });
});
