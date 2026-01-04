<?php

namespace App\Livewire\Admin\DocumentCategories;

use App\Models\DocumentCategory;
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
     * Search query for filtering document categories.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter to show deleted document categories.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'order';

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
     * Document category ID to delete (for confirmation).
     */
    public ?int $documentCategoryToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Document category ID to restore (for confirmation).
     */
    public ?int $documentCategoryToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Document category ID to force delete (for confirmation).
     */
    public ?int $documentCategoryToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', DocumentCategory::class);
    }

    /**
     * Get paginated and filtered document categories.
     */
    #[Computed]
    public function documentCategories(): LengthAwarePaginator
    {
        return DocumentCategory::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['documents'])
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
    public function confirmDelete(int $documentCategoryId): void
    {
        $this->documentCategoryToDelete = $documentCategoryId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a document category (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->documentCategoryToDelete) {
            return;
        }

        $documentCategory = DocumentCategory::withCount(['documents'])->findOrFail($this->documentCategoryToDelete);

        // Check if document category has relationships using the loaded count
        $hasRelations = $documentCategory->documents_count > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->documentCategoryToDelete = null;
            $this->dispatch('document-category-delete-error', [
                'message' => __('No se puede eliminar la categoría porque tiene documentos asociados.'),
            ]);

            return;
        }

        $this->authorize('delete', $documentCategory);

        $documentCategory->delete();

        $this->documentCategoryToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('document-category-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $documentCategoryId): void
    {
        $this->documentCategoryToRestore = $documentCategoryId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted document category.
     */
    public function restore(): void
    {
        if (! $this->documentCategoryToRestore) {
            return;
        }

        $documentCategory = DocumentCategory::onlyTrashed()->findOrFail($this->documentCategoryToRestore);

        $this->authorize('restore', $documentCategory);

        $documentCategory->restore();

        $this->documentCategoryToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('document-category-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $documentCategoryId): void
    {
        $this->documentCategoryToForceDelete = $documentCategoryId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a document category (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->documentCategoryToForceDelete) {
            return;
        }

        $documentCategory = DocumentCategory::onlyTrashed()->withCount(['documents'])->findOrFail($this->documentCategoryToForceDelete);

        $this->authorize('forceDelete', $documentCategory);

        // Verify no relations exist using the loaded count
        if ($documentCategory->documents_count > 0) {
            $this->showForceDeleteModal = false;
            $this->dispatch('document-category-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente la categoría porque tiene documentos asociados.'),
            ]);

            return;
        }

        $documentCategory->forceDelete();

        $this->documentCategoryToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('document-category-force-deleted', [
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
     * Check if user can create document categories.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', DocumentCategory::class) ?? false;
    }

    /**
     * Check if user can view deleted document categories.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', DocumentCategory::class) ?? false;
    }

    /**
     * Check if a document category can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDeleteDocumentCategory(DocumentCategory $documentCategory): bool
    {
        if (! auth()->user()?->can('delete', $documentCategory)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($documentCategory->documents_count ?? 0) === 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.document-categories.index')
            ->layout('components.layouts.app', [
                'title' => __('Categorías de Documentos'),
            ]);
    }
}
