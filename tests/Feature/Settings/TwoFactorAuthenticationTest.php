<?php

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

test('two factor settings page can be rendered', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'))
        ->assertOk()
        ->assertSee('Two Factor Authentication')
        ->assertSee('Disabled');
});

test('two factor settings page requires password confirmation when enabled', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->get(route('two-factor.show'));

    $response->assertRedirect(route('password.confirm'));
});

test('two factor settings page returns forbidden response when two factor is disabled', function () {
    config(['fortify.features' => []]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('two-factor.show'));

    $response->assertForbidden();
});

test('two factor authentication disabled when confirmation abandoned between requests', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => null,
    ])->save();

    $this->actingAs($user);

    $component = Livewire::test('settings.two-factor');

    $component->assertSet('twoFactorEnabled', false);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'two_factor_secret' => null,
        'two_factor_recovery_codes' => null,
    ]);
});

test('two factor authentication can be enabled', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    $component->assertSet('showModal', true)
        ->assertSet('qrCodeSvg', fn ($value) => ! empty($value))
        ->assertSet('manualSetupKey', fn ($value) => ! empty($value));

    if (! $component->get('requiresConfirmation')) {
        $component->assertSet('twoFactorEnabled', true);
    }
});

test('two factor authentication can be enabled without confirmation', function () {
    Features::twoFactorAuthentication([
        'confirm' => false,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    $component->assertSet('twoFactorEnabled', true)
        ->assertSet('showModal', true);
});

test('two factor authentication shows verification step when confirmation required', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->set('showModal', true)
        ->call('showVerificationIfNecessary');

    $component->assertSet('showVerificationStep', true);
});

test('two factor authentication closes modal when confirmation not required', function () {
    Features::twoFactorAuthentication([
        'confirm' => false,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable')
        ->call('showVerificationIfNecessary');

    $component->assertSet('showModal', false)
        ->assertSet('showVerificationStep', false);
});

test('two factor authentication can be confirmed', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    // Enable 2FA first to get a valid secret
    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    $user->refresh();
    expect($user->two_factor_secret)->not->toBeNull();

    $secret = decrypt($user->two_factor_secret);

    // Generate a valid TOTP code
    $google2fa = new \PragmaRX\Google2FA\Google2FA;
    $code = $google2fa->getCurrentOtp($secret);

    $component->set('code', $code)
        ->call('confirmTwoFactor');

    $component->assertSet('twoFactorEnabled', true)
        ->assertSet('showModal', false)
        ->assertSet('showVerificationStep', false);

    $user->refresh();
    expect($user->two_factor_confirmed_at)->not->toBeNull();
});

test('two factor verification can be reset', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->set('code', '123456')
        ->set('showVerificationStep', true)
        ->call('resetVerification');

    $component->assertSet('code', '')
        ->assertSet('showVerificationStep', false);
});

test('two factor authentication can be disabled', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('disable');

    $component->assertSet('twoFactorEnabled', false);

    $user->refresh();
    expect($user->two_factor_secret)->toBeNull()
        ->and($user->two_factor_recovery_codes)->toBeNull();
});

test('two factor modal can be closed', function () {
    Features::twoFactorAuthentication([
        'confirm' => false,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable')
        ->set('code', '123456')
        ->call('closeModal');

    $component->assertSet('showModal', false)
        ->assertSet('showVerificationStep', false)
        ->assertSet('code', '');

    // When confirmation is not required, 2FA is enabled immediately
    $component->assertSet('twoFactorEnabled', true);
});

test('two factor modal config returns correct config when enabled', function () {
    $user = User::factory()->create();

    $user->forceFill([
        'two_factor_secret' => encrypt('test-secret'),
        'two_factor_recovery_codes' => encrypt(json_encode(['code1', 'code2'])),
        'two_factor_confirmed_at' => now(),
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor');

    // twoFactorEnabled is set in mount() based on user state
    $config = $component->get('modalConfig');

    expect($config['title'])->toContain('Enabled')
        ->and($config['buttonText'])->toBe('Close');
});

test('two factor modal config returns correct config for verification step', function () {
    Features::twoFactorAuthentication([
        'confirm' => true,
        'confirmPassword' => true,
    ]);

    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->set('showVerificationStep', true);

    $config = $component->get('modalConfig');

    expect($config['title'])->toContain('Verify')
        ->and($config['description'])->toContain('6-digit')
        ->and($config['buttonText'])->toBe('Continue');
});

test('two factor modal config returns correct config for initial setup', function () {
    $user = User::factory()->withoutTwoFactor()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    // After enable, modal is shown but verification step is false initially
    $config = $component->get('modalConfig');

    expect($config['title'])->toContain('Enable')
        ->and($config['description'])->toContain('QR code')
        ->and($config['buttonText'])->toBe('Continue');
});

test('two factor setup data handles exceptions gracefully', function () {
    $user = User::factory()->create();

    // Create invalid encrypted data that will fail to decrypt
    $user->forceFill([
        'two_factor_secret' => 'invalid-encrypted-data',
    ])->save();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()]);

    $component = Livewire::test('settings.two-factor')
        ->call('enable');

    $component->assertHasErrors('setupData')
        ->assertSet('qrCodeSvg', '')
        ->assertSet('manualSetupKey', '');
});
