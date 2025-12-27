<?php

use App\Livewire\Public\Newsletter\Subscribe;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    App::setLocale('es');
    // Create test programs
    Program::factory()->create([
        'code' => 'KA1xx',
        'name' => 'Educación Escolar',
        'is_active' => true,
        'order' => 1,
    ]);

    Program::factory()->create([
        'code' => 'KA121-VET',
        'name' => 'Formación Profesional',
        'is_active' => true,
        'order' => 2,
    ]);

    Program::factory()->create([
        'code' => 'KA131-HED',
        'name' => 'Educación Superior',
        'is_active' => true,
        'order' => 3,
    ]);
});

describe('Newsletter Subscribe Component - Rendering', function () {
    it('renders the subscribe page', function () {
        $this->get(route('newsletter.subscribe'))
            ->assertOk()
            ->assertSeeLivewire(Subscribe::class);
    });

    it('displays the subscription form', function () {
        Livewire::test(Subscribe::class)
            ->assertSee(__('Correo electrónico'))
            ->assertSee(__('Nombre (opcional)'))
            ->assertSee(__('Programas de interés (opcional)'));
    });

    it('displays available programs', function () {
        Livewire::test(Subscribe::class)
            ->assertSee('Educación Escolar')
            ->assertSee('Formación Profesional')
            ->assertSee('Educación Superior');
    });

    it('does not display inactive programs', function () {
        Program::factory()->create([
            'code' => 'KA999',
            'name' => 'Inactive Program',
            'is_active' => false,
        ]);

        Livewire::test(Subscribe::class)
            ->assertDontSee('Inactive Program');
    });
});

describe('Newsletter Subscribe Component - Validation', function () {
    it('requires email to subscribe', function () {
        Livewire::test(Subscribe::class)
            ->set('email', '')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertHasErrors(['email']);
    });

    it('validates email format', function () {
        Livewire::test(Subscribe::class)
            ->set('email', 'invalid-email')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertHasErrors(['email']);
    });

    it('requires privacy policy acceptance', function () {
        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', false)
            ->call('subscribe')
            ->assertHasErrors(['acceptPrivacy']);
    });

    it('validates unique email', function () {
        NewsletterSubscription::factory()->create([
            'email' => 'existing@example.com',
        ]);

        Livewire::test(Subscribe::class)
            ->set('email', 'existing@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertHasErrors(['email']);
    });

    it('validates program codes exist', function () {
        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('selectedPrograms', ['INVALID-CODE'])
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertHasErrors(['selectedPrograms.0']);
    });

    it('accepts valid program codes', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('selectedPrograms', ['KA1xx', 'KA121-VET'])
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertHasNoErrors(['selectedPrograms'])
            ->assertSet('subscribed', true);

        Mail::assertSent(NewsletterVerificationMail::class);
    });
});

describe('Newsletter Subscribe Component - Subscription Flow', function () {
    it('successfully subscribes with email only', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('subscribed', true)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'test@example.com',
            'is_active' => false,
        ]);

        Mail::assertSent(NewsletterVerificationMail::class, function ($mail) {
            return $mail->subscription->email === 'test@example.com';
        });
    });

    it('successfully subscribes with email and name', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('name', 'John Doe')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('subscribed', true);

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'test@example.com',
            'name' => 'John Doe',
        ]);
    });

    it('successfully subscribes with selected programs', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('selectedPrograms', ['KA1xx', 'KA121-VET'])
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('subscribed', true);

        $subscription = NewsletterSubscription::where('email', 'test@example.com')->first();
        expect($subscription->programs)->toBeArray()
            ->and($subscription->programs)->toContain('KA1xx')
            ->and($subscription->programs)->toContain('KA121-VET');
    });

    it('creates subscription as inactive initially', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe');

        $subscription = NewsletterSubscription::where('email', 'test@example.com')->first();
        expect($subscription->is_active)->toBeFalse()
            ->and($subscription->verified_at)->toBeNull()
            ->and($subscription->verification_token)->not->toBeNull();
    });

    it('generates verification token', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe');

        $subscription = NewsletterSubscription::where('email', 'test@example.com')->first();
        expect($subscription->verification_token)->toBeString()
            ->and(strlen($subscription->verification_token))->toBe(32);
    });

    it('sends verification email', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe');

        Mail::assertSent(NewsletterVerificationMail::class, function ($mail) {
            return $mail->subscription->email === 'test@example.com'
                && $mail->hasTo('test@example.com');
        });
    });

    it('resets form after successful subscription', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('name', 'John Doe')
            ->set('selectedPrograms', ['KA1xx'])
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('email', '')
            ->assertSet('name', '')
            ->assertSet('selectedPrograms', [])
            ->assertSet('acceptPrivacy', false);
    });

    it('stores email in lowercase', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'TEST@EXAMPLE.COM')
            ->set('acceptPrivacy', true)
            ->call('subscribe');

        $this->assertDatabaseHas('newsletter_subscriptions', [
            'email' => 'test@example.com',
        ]);
    });
});

describe('Newsletter Subscribe Component - Program Selection', function () {
    it('can toggle program selection', function () {
        $component = Livewire::test(Subscribe::class);

        $component->call('toggleProgram', 'KA1xx')
            ->assertSet('selectedPrograms', ['KA1xx']);

        $component->call('toggleProgram', 'KA121-VET')
            ->assertSet('selectedPrograms', ['KA1xx', 'KA121-VET']);

        $component->call('toggleProgram', 'KA1xx')
            ->assertSet('selectedPrograms', ['KA121-VET']);
    });

    it('can check if program is selected', function () {
        $component = Livewire::test(Subscribe::class)
            ->set('selectedPrograms', ['KA1xx', 'KA121-VET']);

        $selectedPrograms = $component->get('selectedPrograms');

        expect(in_array('KA1xx', $selectedPrograms, true))->toBeTrue()
            ->and(in_array('KA121-VET', $selectedPrograms, true))->toBeTrue()
            ->and(in_array('KA131-HED', $selectedPrograms, true))->toBeFalse();
    });
});

describe('Newsletter Subscribe Component - Success State', function () {
    it('shows success message after subscription', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('subscribed', true)
            ->assertSee(__('¡Suscripción realizada con éxito!'));
    });

    it('hides form after successful subscription', function () {
        Mail::fake();

        Livewire::test(Subscribe::class)
            ->set('email', 'test@example.com')
            ->set('acceptPrivacy', true)
            ->call('subscribe')
            ->assertSet('subscribed', true);
    });
});
