<?php

use App\Models\NewsletterSubscription;

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

