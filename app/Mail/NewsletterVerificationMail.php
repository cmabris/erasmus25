<?php

namespace App\Mail;

use App\Models\NewsletterSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public NewsletterSubscription $subscription,
        public string $verificationToken
    ) {
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('Verifica tu suscripción a la newsletter Erasmus+'),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.newsletter.verification',
            with: [
                'verificationUrl' => $this->getVerificationUrl(),
                'unsubscribeUrl' => $this->getUnsubscribeUrl(),
            ],
        );
    }

    /**
     * Get the verification URL.
     */
    protected function getVerificationUrl(): string
    {
        return url("/newsletter/verificar/{$this->verificationToken}");
    }

    /**
     * Get the unsubscribe URL.
     */
    protected function getUnsubscribeUrl(): string
    {
        return url("/newsletter/baja/{$this->verificationToken}");
    }
}

