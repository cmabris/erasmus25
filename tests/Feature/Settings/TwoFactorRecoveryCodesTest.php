<?php

use App\Livewire\Settings\TwoFactor\RecoveryCodes;
use App\Models\User;
use Laravel\Fortify\Features;
use Livewire\Livewire;

beforeEach(function () {
    if (! Features::canManageTwoFactorAuthentication()) {
        $this->markTestSkipped('Two-factor authentication is not enabled.');
    }

    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);
});

test('recovery codes component loads codes when two factor is enabled', function () {
    $user = User::factory()->create();

    $recoveryCodes = ['code1', 'code2', 'code3'];
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode($recoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(RecoveryCodes::class);

    $component->assertSet('recoveryCodes', $recoveryCodes);
});

test('recovery codes component returns empty array when two factor is not enabled', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(RecoveryCodes::class);

    $component->assertSet('recoveryCodes', []);
});

test('recovery codes component returns empty array when recovery codes are null', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => null,
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(RecoveryCodes::class);

    $component->assertSet('recoveryCodes', []);
});

test('recovery codes component handles decryption errors gracefully', function () {
    $user = User::factory()->create();

    // Create invalid encrypted data that will fail to decrypt
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => 'invalid-encrypted-data',
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(RecoveryCodes::class);

    $component->assertHasErrors('recoveryCodes')
        ->assertSet('recoveryCodes', []);
});

test('recovery codes can be regenerated', function () {
    $user = User::factory()->create();

    $oldRecoveryCodes = ['code1', 'code2', 'code3'];
    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode($oldRecoveryCodes)),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test(RecoveryCodes::class)
        ->assertSet('recoveryCodes', $oldRecoveryCodes)
        ->call('regenerateRecoveryCodes');

    $user->refresh();

    // New recovery codes should be different
    $newRecoveryCodes = json_decode(decrypt($user->two_factor_recovery_codes), true);

    expect($newRecoveryCodes)->not->toBe($oldRecoveryCodes)
        ->and($component->get('recoveryCodes'))->toBe($newRecoveryCodes);
});
