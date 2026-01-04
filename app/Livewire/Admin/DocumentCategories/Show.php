<?php

namespace App\Livewire\Admin\DocumentCategories;

use App\Models\DocumentCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The document category being displayed.
     */
    public DocumentCategory $documentCategory;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(DocumentCategory $document_category): void
    {
        $this->authorize('view', $document_category);

        // Load relationships with eager loading to avoid N+1 queries
        $this->documentCategory = $document_category->load([
            'documents' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['documents']);
    }

    /**
     * Get statistics for the document category.
     * Uses loaded counts to avoid N+1 queries.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_documents' => $this->documentCategory->documents_count ?? $this->documentCategory->documents()->count(),
        ];
    }

    /**
     * Delete the document category (soft delete).
     */
    public function delete(): void
    {
        // Refresh the count to ensure we have the latest data
        $this->documentCategory->refresh();
        $this->documentCategory->loadCount(['documents']);

        // Check if document category has relationships using the loaded count
        $hasRelations = ($this->documentCategory->documents_count ?? 0) > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('document-category-delete-error', [
                'message' => __('No se puede eliminar la categoría porque tiene documentos asociados.'),
            ]);

            return;
        }

        $this->authorize('delete', $this->documentCategory);

        $this->documentCategory->delete();

        $this->dispatch('document-category-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.document-categories.index'), navigate: true);
    }

    /**
     * Restore the document category.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->documentCategory);

        $this->documentCategory->restore();

        $this->dispatch('document-category-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);

        // Reload the document category to refresh the view
        $this->documentCategory->refresh();
    }

    /**
     * Permanently delete the document category.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->documentCategory);

        // Refresh the count to ensure we have the latest data
        $this->documentCategory->refresh();
        $this->documentCategory->loadCount(['documents']);

        // Check relations one more time using the loaded count
        $hasRelations = ($this->documentCategory->documents_count ?? 0) > 0;

        if ($hasRelations) {
            $this->dispatch('document-category-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente la categoría porque tiene documentos asociados.'),
            ]);

            return;
        }

        $this->documentCategory->forceDelete();

        $this->dispatch('document-category-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.document-categories.index'), navigate: true);
    }

    /**
     * Check if the document category can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->documentCategory)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($this->documentCategory->documents_count ?? 0) === 0;
    }

    /**
     * Check if the document category has relationships.
     * Uses the loaded count to avoid additional queries.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return ($this->documentCategory->documents_count ?? 0) > 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.document-categories.show')
            ->layout('components.layouts.app', [
                'title' => $this->documentCategory->name ?? 'Categoría',
            ]);
    }
}
