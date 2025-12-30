<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Models\Call;
use App\Models\Resolution;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * The call that owns these resolutions.
     */
    public Call $call;

    /**
     * Search query for filtering resolutions.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by resolution type.
     */
    #[Url(as: 'tipo')]
    public string $filterType = '';

    /**
     * Filter by published status.
     */
    #[Url(as: 'publicada')]
    public string $filterPublished = '';

    /**
     * Filter by call phase.
     */
    #[Url(as: 'fase')]
    public string $filterPhase = '';

    /**
     * Filter to show deleted resolutions.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'official_date';

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
     * Resolution ID to delete (for confirmation).
     */
    public ?int $resolutionToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Resolution ID to restore (for confirmation).
     */
    public ?int $resolutionToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Resolution ID to force delete (for confirmation).
     */
    public ?int $resolutionToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(Call $call): void
    {
        $this->authorize('viewAny', Resolution::class);

        // Load call with minimal relationships for display
        $this->call = $call->load(['program', 'academicYear']);
    }

    /**
     * Get paginated and filtered resolutions.
     */
    #[Computed]
    public function resolutions(): LengthAwarePaginator
    {
        return Resolution::query()
            ->where('call_id', $this->call->id)
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filterType, fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterPublished === '1', fn ($query) => $query->whereNotNull('published_at'))
            ->when($this->filterPublished === '0', fn ($query) => $query->whereNull('published_at'))
            ->when($this->filterPhase, fn ($query) => $query->where('call_phase_id', $this->filterPhase))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->with([
                'call' => fn ($query) => $query->select('id', 'title', 'program_id', 'academic_year_id'),
                'callPhase' => fn ($query) => $query->select('id', 'call_id', 'name', 'phase_type'),
                'creator' => fn ($query) => $query->select('id', 'name', 'email'),
                'media' => fn ($query) => $query->where('collection_name', 'resolutions'),
            ])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
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
     * Publish a resolution.
     */
    public function publish(int $resolutionId): void
    {
        $resolution = Resolution::findOrFail($resolutionId);

        $this->authorize('publish', $resolution);

        $resolution->published_at = now();
        $resolution->save();

        $this->dispatch('resolution-published', [
            'message' => __('La resolución ":title" ha sido publicada correctamente.', ['title' => $resolution->title]),
            'title' => __('Resolución publicada'),
        ]);
    }

    /**
     * Unpublish a resolution.
     */
    public function unpublish(int $resolutionId): void
    {
        $resolution = Resolution::findOrFail($resolutionId);

        $this->authorize('publish', $resolution);

        $resolution->published_at = null;
        $resolution->save();

        $this->dispatch('resolution-unpublished', [
            'message' => __('La resolución ":title" ha sido despublicada correctamente.', ['title' => $resolution->title]),
            'title' => __('Resolución despublicada'),
        ]);
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $resolutionId): void
    {
        $this->resolutionToDelete = $resolutionId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a resolution (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->resolutionToDelete) {
            return;
        }

        $resolution = Resolution::findOrFail($this->resolutionToDelete);

        $this->authorize('delete', $resolution);

        $resolutionTitle = $resolution->title;
        $resolution->delete();

        $this->resolutionToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('resolution-deleted', [
            'message' => __('La resolución ":title" ha sido eliminada correctamente. Puede restaurarla desde la sección de eliminados.', ['title' => $resolutionTitle]),
            'title' => __('Resolución eliminada'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $resolutionId): void
    {
        $this->resolutionToRestore = $resolutionId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted resolution.
     */
    public function restore(): void
    {
        if (! $this->resolutionToRestore) {
            return;
        }

        $resolution = Resolution::onlyTrashed()->findOrFail($this->resolutionToRestore);

        $this->authorize('restore', $resolution);

        $resolutionTitle = $resolution->title;
        $resolution->restore();

        $this->resolutionToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('resolution-restored', [
            'message' => __('La resolución ":title" ha sido restaurada correctamente.', ['title' => $resolutionTitle]),
            'title' => __('Resolución restaurada'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $resolutionId): void
    {
        $this->resolutionToForceDelete = $resolutionId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a resolution (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->resolutionToForceDelete) {
            return;
        }

        $resolution = Resolution::onlyTrashed()->findOrFail($this->resolutionToForceDelete);

        $this->authorize('forceDelete', $resolution);

        // Note: Resolutions don't have critical relationships that would prevent deletion
        // They are child entities of Calls and CallPhases, so they can be safely deleted
        // If in the future there are relationships (e.g., notifications, audit logs),
        // they should be validated here

        $resolutionTitle = $resolution->title;
        $resolution->forceDelete();

        $this->resolutionToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('resolution-force-deleted', [
            'message' => __('La resolución ":title" ha sido eliminada permanentemente del sistema. Esta acción no se puede revertir.', ['title' => $resolutionTitle]),
            'title' => __('Resolución eliminada permanentemente'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterPublished', 'filterPhase', 'showDeleted']);
        $this->showDeleted = '0';
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
     * Handle filter changes.
     */
    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPublished(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPhase(): void
    {
        $this->resetPage();
    }

    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Get resolution type options.
     */
    public function getTypeOptions(): array
    {
        return [
            'provisional' => __('Provisional'),
            'definitivo' => __('Definitivo'),
            'alegaciones' => __('Alegaciones'),
        ];
    }

    /**
     * Get resolution type badge color.
     */
    public function getTypeColor(string $type): string
    {
        return match ($type) {
            'provisional' => 'yellow',
            'definitivo' => 'green',
            'alegaciones' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get status badge color for call.
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'borrador' => 'gray',
            'abierta' => 'green',
            'cerrada' => 'yellow',
            'en_baremacion' => 'blue',
            'resuelta' => 'purple',
            'archivada' => 'zinc',
            default => 'gray',
        };
    }

    /**
     * Get call phases for filter dropdown.
     */
    #[Computed]
    public function callPhases(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->call->phases()
            ->orderBy('order')
            ->get(['id', 'name', 'phase_type']);
    }

    /**
     * Check if resolution has PDF.
     * Uses eager loaded media to avoid N+1 queries.
     */
    public function hasPdf(Resolution $resolution): bool
    {
        // Use eager loaded media if available, otherwise fallback to query
        if ($resolution->relationLoaded('media')) {
            return $resolution->media->where('collection_name', 'resolutions')->isNotEmpty();
        }

        return $resolution->hasMedia('resolutions');
    }

    /**
     * Check if user can create resolutions.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Resolution::class) ?? false;
    }

    /**
     * Check if user can view deleted resolutions.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', Resolution::class) ?? false;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.resolutions.index')
            ->layout('components.layouts.app', [
                'title' => __('Resoluciones de Convocatoria'),
            ]);
    }
}
