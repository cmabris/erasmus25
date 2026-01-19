<?php

use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/*
|--------------------------------------------------------------------------
| NewsletterVerificationMail Tests
|--------------------------------------------------------------------------
|
| Tests para cubrir el mailable de verificación de newsletter.
| Objetivo: Aumentar cobertura de 30.77% a 100%.
|
*/

describe('NewsletterVerificationMail', function () {
    beforeEach(function () {
        $this->subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'programs' => ['KA1', 'KA2'],
        ]);
        $this->token = 'test-verification-token-123';
    });

    it('can be instantiated with subscription and token', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);

        expect($mail->subscription)->toBe($this->subscription);
        expect($mail->verificationToken)->toBe($this->token);
    });

    it('has correct envelope subject', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $envelope = $mail->envelope();

        expect($envelope->subject)->toBe(__('Verifica tu suscripción a la newsletter Erasmus+'));
    });

    it('has correct content view', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $content = $mail->content();

        expect($content->view)->toBe('emails.newsletter.verification');
    });

    it('passes verification url to view', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $content = $mail->content();

        expect($content->with)->toHaveKey('verificationUrl');
        expect($content->with['verificationUrl'])->toContain('/newsletter/verificar/');
        expect($content->with['verificationUrl'])->toContain($this->token);
    });

    it('passes unsubscribe url to view', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $content = $mail->content();

        expect($content->with)->toHaveKey('unsubscribeUrl');
        expect($content->with['unsubscribeUrl'])->toContain('/newsletter/baja/');
        expect($content->with['unsubscribeUrl'])->toContain($this->token);
    });

    it('generates correct verification url format', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $content = $mail->content();

        $expectedUrl = url("/newsletter/verificar/{$this->token}");
        expect($content->with['verificationUrl'])->toBe($expectedUrl);
    });

    it('generates correct unsubscribe url format', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $content = $mail->content();

        $expectedUrl = url("/newsletter/baja/{$this->token}");
        expect($content->with['unsubscribeUrl'])->toBe($expectedUrl);
    });
});

describe('NewsletterVerificationMail Rendering', function () {
    beforeEach(function () {
        $this->subscription = NewsletterSubscription::factory()->create([
            'email' => 'test@example.com',
            'name' => 'Test User',
            'programs' => ['KA1', 'KA2'],
        ]);
        $this->token = 'render-test-token';
    });

    it('can be rendered as string', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toBeString();
        expect($rendered)->toContain('<!DOCTYPE html>');
    });

    it('renders verification button with correct url', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $rendered = $mail->render();

        $expectedUrl = url("/newsletter/verificar/{$this->token}");
        expect($rendered)->toContain($expectedUrl);
    });

    it('renders unsubscribe link with correct url', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $rendered = $mail->render();

        $expectedUrl = url("/newsletter/baja/{$this->token}");
        expect($rendered)->toContain($expectedUrl);
    });

    it('renders subscription name when present', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toContain('Test User');
    });

    it('renders programs when subscription has programs', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toContain('KA1');
        expect($rendered)->toContain('KA2');
    });

    it('works with subscription without name', function () {
        $subscriptionWithoutName = NewsletterSubscription::factory()->create([
            'email' => 'noname@example.com',
            'name' => null,
            'programs' => [],
        ]);

        $mail = new NewsletterVerificationMail($subscriptionWithoutName, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toBeString();
        expect($rendered)->toContain('<!DOCTYPE html>');
    });

    it('works with subscription without programs', function () {
        $subscriptionWithoutPrograms = NewsletterSubscription::factory()->create([
            'email' => 'noprograms@example.com',
            'name' => 'User Without Programs',
            'programs' => null,
        ]);

        $mail = new NewsletterVerificationMail($subscriptionWithoutPrograms, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toBeString();
        expect($rendered)->toContain('User Without Programs');
    });

    it('works with empty programs array', function () {
        $subscriptionWithEmptyPrograms = NewsletterSubscription::factory()->create([
            'email' => 'empty@example.com',
            'name' => 'User Empty Programs',
            'programs' => [],
        ]);

        $mail = new NewsletterVerificationMail($subscriptionWithEmptyPrograms, $this->token);
        $rendered = $mail->render();

        expect($rendered)->toBeString();
    });
});

describe('NewsletterVerificationMail Assertions', function () {
    beforeEach(function () {
        $this->subscription = NewsletterSubscription::factory()->create([
            'email' => 'assertions@example.com',
            'name' => 'Assertions User',
        ]);
        $this->token = 'assertions-token';
    });

    it('is mailable', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);

        expect($mail)->toBeInstanceOf(\Illuminate\Mail\Mailable::class);
    });

    it('uses Queueable trait', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $traits = class_uses_recursive($mail);

        expect($traits)->toContain(\Illuminate\Bus\Queueable::class);
    });

    it('uses SerializesModels trait', function () {
        $mail = new NewsletterVerificationMail($this->subscription, $this->token);
        $traits = class_uses_recursive($mail);

        expect($traits)->toContain(\Illuminate\Queue\SerializesModels::class);
    });

});
