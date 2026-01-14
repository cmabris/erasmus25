<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterSubscription;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The subscription being displayed.
     */
    public NewsletterSubscription $subscription;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(NewsletterSubscription $newsletter_subscription): void
    {
        $this->authorize('view', $newsletter_subscription);

        $this->subscription = $newsletter_subscription;
    }

    /**
     * Get program models for this subscription.
     */
    #[Computed]
    public function programModels(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $this->subscription->programs || ! is_array($this->subscription->programs) || empty($this->subscription->programs)) {
            return Program::query()->whereRaw('1 = 0')->get();
        }

        return Program::whereIn('code', $this->subscription->programs)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Delete the subscription (hard delete).
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->subscription);

        $this->subscription->delete();

        $this->dispatch('newsletter-subscription-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.newsletter.index'), navigate: true);
    }

    /**
     * Check if user can delete this subscription.
     */
    public function canDelete(): bool
    {
        return auth()->user()?->can('delete', $this->subscription) ?? false;
    }

    /**
     * Get status badge variant for the subscription.
     */
    public function getStatusBadge(): string
    {
        return $this->subscription->is_active ? 'success' : 'danger';
    }

    /**
     * Get verification badge variant for the subscription.
     */
    public function getVerificationBadge(): string
    {
        return $this->subscription->isVerified() ? 'success' : 'warning';
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.newsletter.show')
            ->layout('components.layouts.app', [
                'title' => $this->subscription->email ?? 'Suscripción',
            ]);
    }
}
