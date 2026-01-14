<?php

namespace App\Livewire\Admin\Newsletter;

use App\Models\NewsletterSubscription;
use App\Models\Program;
use App\Support\Permissions;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * Search query for filtering subscriptions.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by program code.
     */
    #[Url(as: 'programa')]
    public ?string $filterProgram = null;

    /**
     * Filter by status.
     * Values: 'activo', 'inactivo'
     */
    #[Url(as: 'estado')]
    public ?string $filterStatus = null;

    /**
     * Filter by verification status.
     * Values: 'verificado', 'no-verificado'
     */
    #[Url(as: 'verificacion')]
    public ?string $filterVerification = null;

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'subscribed_at';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'desc';

    /**
     * Number of items per page.
     */
    #[Url(as: 'por-pagina')]
    public int $perPage = 15;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Subscription ID to delete (for confirmation).
     */
    public ?int $subscriptionToDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', NewsletterSubscription::class);
    }

    /**
     * Get paginated and filtered subscriptions.
     */
    #[Computed]
    public function subscriptions(): LengthAwarePaginator
    {
        return NewsletterSubscription::query()
            ->when($this->filterProgram, function ($query) {
                $query->whereJsonContains('programs', $this->filterProgram);
            })
            ->when($this->filterStatus === 'activo', fn ($query) => $query->where('is_active', true))
            ->when($this->filterStatus === 'inactivo', fn ($query) => $query->where('is_active', false))
            ->when($this->filterVerification === 'verificado', fn ($query) => $query->whereNotNull('verified_at'))
            ->when($this->filterVerification === 'no-verificado', fn ($query) => $query->whereNull('verified_at'))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('email', 'like', "%{$this->search}%")
                        ->orWhere('name', 'like', "%{$this->search}%");
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('email', 'asc')
            ->paginate($this->perPage);
    }

    /**
     * Get all programs for filter dropdown.
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get statistics for subscriptions.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total' => NewsletterSubscription::count(),
            'active' => NewsletterSubscription::where('is_active', true)->count(),
            'verified' => NewsletterSubscription::whereNotNull('verified_at')->count(),
        ];
    }

    /**
     * Sort by field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterProgram', 'filterStatus', 'filterVerification']);
        $this->resetPage();
    }

    /**
     * Handle search input changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle program filter changes.
     */
    public function updatedFilterProgram(): void
    {
        $this->resetPage();
    }

    /**
     * Handle status filter changes.
     */
    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    /**
     * Handle verification filter changes.
     */
    public function updatedFilterVerification(): void
    {
        $this->resetPage();
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $subscriptionId): void
    {
        $this->subscriptionToDelete = $subscriptionId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a subscription (hard delete).
     */
    public function delete(): void
    {
        if (! $this->subscriptionToDelete) {
            return;
        }

        $subscription = NewsletterSubscription::findOrFail($this->subscriptionToDelete);

        $this->authorize('delete', $subscription);

        $subscription->delete();

        $this->subscriptionToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('newsletter-subscription-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Export subscriptions to Excel.
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('export', NewsletterSubscription::class);

        $filters = [
            'search' => $this->search,
            'filterProgram' => $this->filterProgram,
            'filterStatus' => $this->filterStatus,
            'filterVerification' => $this->filterVerification,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $fileName = 'newsletter-subscriptions-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(
            new \App\Exports\NewsletterSubscriptionsExport($filters),
            $fileName
        );
    }

    /**
     * Check if user can delete subscriptions.
     */
    public function canDelete(): bool
    {
        return auth()->user()?->can(Permissions::NEWSLETTER_DELETE) ?? false;
    }

    /**
     * Check if user can export subscriptions.
     */
    public function canExport(): bool
    {
        return auth()->user()?->can('export', NewsletterSubscription::class) ?? false;
    }

    /**
     * Get status badge variant for a subscription.
     */
    public function getStatusBadge(NewsletterSubscription $subscription): string
    {
        return $subscription->is_active ? 'success' : 'danger';
    }

    /**
     * Get verification badge variant for a subscription.
     */
    public function getVerificationBadge(NewsletterSubscription $subscription): string
    {
        return $subscription->isVerified() ? 'success' : 'warning';
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.newsletter.index')
            ->layout('components.layouts.app', [
                'title' => __('Suscripciones Newsletter'),
            ]);
    }
}
