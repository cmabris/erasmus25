<?php

namespace App\Livewire\Public\Newsletter;

use App\Models\NewsletterSubscription;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Unsubscribe extends Component
{
    /**
     * Verification token from URL (optional).
     */
    public ?string $token = null;

    /**
     * Email address for unsubscription (when no token).
     */
    public string $email = '';

    /**
     * Unsubscription status.
     */
    public ?string $status = null; // 'success', 'already_unsubscribed', 'not_found', 'error'

    /**
     * Status message.
     */
    public ?string $message = null;

    /**
     * Subscription found by token or email.
     */
    public ?NewsletterSubscription $subscription = null;

    /**
     * Mount the component.
     */
    public function mount(?string $token = null): void
    {
        $this->token = $token;

        // If token is provided, try to find and unsubscribe automatically
        if ($this->token) {
            $this->unsubscribeByToken();
        }
    }

    /**
     * Unsubscribe by token (automatic).
     */
    protected function unsubscribeByToken(): void
    {
        // Find subscription by token
        $this->subscription = NewsletterSubscription::where('verification_token', $this->token)
            ->first();

        // Check if subscription exists
        if (! $this->subscription) {
            $this->status = 'not_found';
            $this->message = __('No se encontró ninguna suscripción con este token.');

            return;
        }

        // Check if already unsubscribed
        if (! $this->subscription->isActive()) {
            $this->status = 'already_unsubscribed';
            $this->message = __('Esta suscripción ya ha sido cancelada anteriormente.');

            return;
        }

        // Unsubscribe
        try {
            $this->subscription->unsubscribe();
            $this->status = 'success';
            $this->message = __('Tu suscripción ha sido cancelada correctamente. Ya no recibirás más emails de nuestra newsletter.');
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->message = __('Ha ocurrido un error al cancelar tu suscripción. Por favor, intenta nuevamente más tarde.');
        }
    }

    /**
     * Unsubscribe by email (manual form).
     */
    public function unsubscribeByEmail(): void
    {
        // Validate email
        $this->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => __('El correo electrónico es obligatorio.'),
            'email.email' => __('El correo electrónico debe ser válido.'),
        ]);

        // Find subscription by email
        $this->subscription = NewsletterSubscription::where('email', strtolower($this->email))
            ->first();

        // Check if subscription exists
        if (! $this->subscription) {
            $this->status = 'not_found';
            $this->message = __('No se encontró ninguna suscripción con este correo electrónico.');

            return;
        }

        // Check if already unsubscribed
        if (! $this->subscription->isActive()) {
            $this->status = 'already_unsubscribed';
            $this->message = __('Esta suscripción ya ha sido cancelada anteriormente.');

            return;
        }

        // Unsubscribe
        try {
            $this->subscription->unsubscribe();
            $this->status = 'success';
            $this->message = __('Tu suscripción ha sido cancelada correctamente. Ya no recibirás más emails de nuestra newsletter.');
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->message = __('Ha ocurrido un error al cancelar tu suscripción. Por favor, intenta nuevamente más tarde.');
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.newsletter.unsubscribe')
            ->layout('components.layouts.public', [
                'title' => __('Cancelar Suscripción - Newsletter Erasmus+'),
                'description' => __('Cancela tu suscripción a la newsletter del programa Erasmus+.'),
            ]);
    }
}

