<?php

namespace App\Livewire\Admin\NewsTags;

use App\Models\NewsTag;
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
     * Search query for filtering news tags.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter to show deleted news tags.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'name';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'asc';

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
     * News tag ID to delete (for confirmation).
     */
    public ?int $newsTagToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * News tag ID to restore (for confirmation).
     */
    public ?int $newsTagToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * News tag ID to force delete (for confirmation).
     */
    public ?int $newsTagToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', NewsTag::class);
    }

    /**
     * Get paginated and filtered news tags.
     */
    #[Computed]
    public function newsTags(): LengthAwarePaginator
    {
        return NewsTag::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['newsPosts'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('name', 'asc')
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
     * Confirm delete action.
     */
    public function confirmDelete(int $newsTagId): void
    {
        $this->newsTagToDelete = $newsTagId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a news tag (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->newsTagToDelete) {
            return;
        }

        $newsTag = NewsTag::withCount(['newsPosts'])->findOrFail($this->newsTagToDelete);

        // Check if news tag has relationships using the loaded count
        $hasRelations = $newsTag->news_posts_count > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->newsTagToDelete = null;
            $this->dispatch('news-tag-delete-error', [
                'message' => __('No se puede eliminar la etiqueta porque tiene noticias asociadas.'),
            ]);

            return;
        }

        $this->authorize('delete', $newsTag);

        $newsTag->delete();

        $this->newsTagToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('news-tag-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $newsTagId): void
    {
        $this->newsTagToRestore = $newsTagId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted news tag.
     */
    public function restore(): void
    {
        if (! $this->newsTagToRestore) {
            return;
        }

        $newsTag = NewsTag::onlyTrashed()->findOrFail($this->newsTagToRestore);

        $this->authorize('restore', $newsTag);

        $newsTag->restore();

        $this->newsTagToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('news-tag-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $newsTagId): void
    {
        $this->newsTagToForceDelete = $newsTagId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a news tag (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->newsTagToForceDelete) {
            return;
        }

        $newsTag = NewsTag::onlyTrashed()->withCount(['newsPosts'])->findOrFail($this->newsTagToForceDelete);

        $this->authorize('forceDelete', $newsTag);

        // Verify no relations exist using the loaded count
        if ($newsTag->news_posts_count > 0) {
            $this->showForceDeleteModal = false;
            $this->dispatch('news-tag-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente la etiqueta porque tiene noticias asociadas.'),
            ]);

            return;
        }

        $newsTag->forceDelete();

        $this->newsTagToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('news-tag-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'showDeleted']);
        $this->showDeleted = '0'; // Reset to '0' (no)
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
    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create news tags.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', NewsTag::class) ?? false;
    }

    /**
     * Check if user can view deleted news tags.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', NewsTag::class) ?? false;
    }

    /**
     * Check if a news tag can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDeleteNewsTag(NewsTag $newsTag): bool
    {
        if (! auth()->user()?->can('delete', $newsTag)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($newsTag->news_posts_count ?? 0) === 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news-tags.index')
            ->layout('components.layouts.app', [
                'title' => __('Etiquetas de Noticias'),
            ]);
    }
}
