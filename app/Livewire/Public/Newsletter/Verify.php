<?php

namespace App\Livewire\Public\Newsletter;

use App\Models\NewsletterSubscription;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Verify extends Component
{
    /**
     * Verification token from URL.
     */
    public string $token;

    /**
     * Subscription found by token.
     */
    public ?NewsletterSubscription $subscription = null;

    /**
     * Verification status.
     */
    public ?string $status = null; // 'success', 'already_verified', 'invalid', 'error'

    /**
     * Status message.
     */
    public ?string $message = null;

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->verifySubscription();
    }

    /**
     * Verify the subscription.
     */
    protected function verifySubscription(): void
    {
        // Find subscription by token
        $this->subscription = NewsletterSubscription::where('verification_token', $this->token)
            ->first();

        // Check if subscription exists
        if (! $this->subscription) {
            $this->status = 'invalid';
            $this->message = __('El token de verificación no es válido o ha expirado.');

            return;
        }

        // Check if already verified
        if ($this->subscription->isVerified()) {
            $this->status = 'already_verified';
            $this->message = __('Esta suscripción ya ha sido verificada anteriormente.');

            return;
        }

        // Verify the subscription
        try {
            $this->subscription->verify();
            $this->status = 'success';
            $this->message = __('¡Tu suscripción ha sido verificada con éxito! A partir de ahora recibirás nuestras newsletters.');
        } catch (\Exception $e) {
            $this->status = 'error';
            $this->message = __('Ha ocurrido un error al verificar tu suscripción. Por favor, intenta nuevamente más tarde.');
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.newsletter.verify')
            ->layout('components.layouts.public', [
                'title' => __('Verificación de Suscripción - Newsletter Erasmus+'),
                'description' => __('Verifica tu suscripción a la newsletter del programa Erasmus+.'),
            ]);
    }
}

