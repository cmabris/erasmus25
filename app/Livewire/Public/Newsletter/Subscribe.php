<?php

namespace App\Livewire\Public\Newsletter;

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Subscribe extends Component
{
    /**
     * Email address for subscription.
     */
    public string $email = '';

    /**
     * Optional name for subscription.
     */
    public string $name = '';

    /**
     * Selected program codes.
     *
     * @var array<int, string>
     */
    public array $selectedPrograms = [];

    /**
     * Privacy policy acceptance.
     */
    public bool $acceptPrivacy = false;

    /**
     * Subscription success status.
     */
    public bool $subscribed = false;

    /**
     * Get available active programs.
     *
     * @return Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): Collection
    {
        return Program::where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Subscribe to the newsletter.
     */
    public function subscribe(): void
    {
        // Validate privacy policy acceptance
        $this->validate([
            'acceptPrivacy' => ['accepted'],
        ], [
            'acceptPrivacy.accepted' => __('Debe aceptar la política de privacidad para suscribirse.'),
        ]);

        // Get Form Request rules and adapt for selectedPrograms
        $formRequest = new StoreNewsletterSubscriptionRequest();
        $rules = $formRequest->rules();
        
        // Adapt rules to use selectedPrograms instead of programs
        $rules['selectedPrograms'] = $rules['programs'] ?? ['nullable', 'array'];
        $rules['selectedPrograms.*'] = $rules['programs.*'] ?? ['string'];
        unset($rules['programs'], $rules['programs.*']);
        
        // Validate subscription data
        $validated = $this->validate($rules);

        // Create subscription (initially inactive until verified)
        $subscription = NewsletterSubscription::create([
            'email' => strtolower($validated['email']),
            'name' => $validated['name'] ?? null,
            'programs' => ! empty($validated['selectedPrograms']) ? $validated['selectedPrograms'] : null,
            'is_active' => false,
            'subscribed_at' => now(),
            'verification_token' => null,
            'verified_at' => null,
        ]);

        // Generate verification token
        $token = $subscription->generateVerificationToken();

        // Send verification email
        Mail::to($subscription->email)->send(new NewsletterVerificationMail($subscription, $token));

        // Mark as subscribed and reset form
        $this->subscribed = true;
        $this->resetForm();

        // Dispatch event for success message
        session()->flash('newsletter-subscribed', true);
    }

    /**
     * Reset the form after successful subscription.
     */
    public function resetForm(): void
    {
        $this->reset(['email', 'name', 'selectedPrograms', 'acceptPrivacy']);
    }

    /**
     * Toggle program selection.
     */
    public function toggleProgram(string $programCode): void
    {
        if (in_array($programCode, $this->selectedPrograms, true)) {
            $this->selectedPrograms = array_values(array_filter(
                $this->selectedPrograms,
                fn ($code) => $code !== $programCode
            ));
        } else {
            $this->selectedPrograms[] = $programCode;
        }
    }

    /**
     * Check if a program is selected.
     */
    public function isProgramSelected(string $programCode): bool
    {
        return in_array($programCode, $this->selectedPrograms, true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.newsletter.subscribe')
            ->layout('components.layouts.public', [
                'title' => __('Suscripción a Newsletter - Erasmus+ Centro'),
                'description' => __('Suscríbete a nuestra newsletter para recibir las últimas noticias, convocatorias y eventos del programa Erasmus+.'),
            ]);
    }
}

