<?php

namespace App\Livewire\Admin\Documents;

use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
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
     * Search query for filtering documents.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by category.
     */
    #[Url(as: 'categoria')]
    public ?int $categoryId = null;

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public ?int $programId = null;

    /**
     * Filter by academic year.
     */
    #[Url(as: 'anio')]
    public ?int $academicYearId = null;

    /**
     * Filter by document type.
     */
    #[Url(as: 'tipo')]
    public ?string $documentType = null;

    /**
     * Filter by active status.
     */
    #[Url(as: 'activo')]
    public ?string $isActive = null;

    /**
     * Filter to show deleted documents.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'created_at';

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
     * Document ID to delete (for confirmation).
     */
    public ?int $documentToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Document ID to restore (for confirmation).
     */
    public ?int $documentToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Document ID to force delete (for confirmation).
     */
    public ?int $documentToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Document::class);
    }

    /**
     * Get paginated and filtered documents.
     */
    #[Computed]
    public function documents(): LengthAwarePaginator
    {
        return Document::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->categoryId, fn ($query) => $query->where('category_id', $this->categoryId))
            ->when($this->programId, fn ($query) => $query->where('program_id', $this->programId))
            ->when($this->academicYearId, fn ($query) => $query->where('academic_year_id', $this->academicYearId))
            ->when($this->documentType, fn ($query) => $query->where('document_type', $this->documentType))
            ->when($this->isActive !== null && $this->isActive !== '', fn ($query) => $query->where('is_active', $this->isActive === '1'))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->with(['category', 'program', 'academicYear', 'creator', 'updater', 'media'])
            ->withCount(['mediaConsents'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Get all categories for filter dropdown (cached).
     */
    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return DocumentCategory::getCachedAll();
    }

    /**
     * Get all programs for filter dropdown (cached).
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::getCachedActive();
    }

    /**
     * Get all academic years for filter dropdown (cached).
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::getCachedAll();
    }

    /**
     * Get document type options.
     */
    public function getDocumentTypeOptions(): array
    {
        return [
            'convocatoria' => __('Convocatoria'),
            'modelo' => __('Modelo'),
            'seguro' => __('Seguro'),
            'consentimiento' => __('Consentimiento'),
            'guia' => __('Guía'),
            'faq' => __('FAQ'),
            'otro' => __('Otro'),
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
     * Confirm delete action.
     */
    public function confirmDelete(int $documentId): void
    {
        $this->documentToDelete = $documentId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a document (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->documentToDelete) {
            return;
        }

        $document = Document::withCount(['mediaConsents'])->findOrFail($this->documentToDelete);

        // Check if document has relationships using the loaded count
        $hasRelations = $document->media_consents_count > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->documentToDelete = null;
            $this->dispatch('document-delete-error', [
                'message' => __('No se puede eliminar el documento porque tiene consentimientos de medios asociados.'),
            ]);

            return;
        }

        $this->authorize('delete', $document);

        $document->delete();

        $this->documentToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('document-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $documentId): void
    {
        $this->documentToRestore = $documentId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted document.
     */
    public function restore(): void
    {
        if (! $this->documentToRestore) {
            return;
        }

        $document = Document::onlyTrashed()->findOrFail($this->documentToRestore);

        $this->authorize('restore', $document);

        $document->restore();

        $this->documentToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('document-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $documentId): void
    {
        $this->documentToForceDelete = $documentId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a document (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->documentToForceDelete) {
            return;
        }

        $document = Document::onlyTrashed()->withCount(['mediaConsents'])->findOrFail($this->documentToForceDelete);

        $this->authorize('forceDelete', $document);

        // Verify no relations exist using the loaded count
        if ($document->media_consents_count > 0) {
            $this->showForceDeleteModal = false;
            $this->dispatch('document-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente el documento porque tiene consentimientos de medios asociados.'),
            ]);

            return;
        }

        $document->forceDelete();

        $this->documentToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('document-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryId', 'programId', 'academicYearId', 'documentType', 'isActive', 'showDeleted']);
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
     * Check if user can create documents.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Document::class) ?? false;
    }

    /**
     * Check if user can view deleted documents.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', Document::class) ?? false;
    }

    /**
     * Check if a document can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDeleteDocument(Document $document): bool
    {
        if (! auth()->user()?->can('delete', $document)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($document->media_consents_count ?? 0) === 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.documents.index')
            ->layout('components.layouts.app', [
                'title' => __('Documentos'),
            ]);
    }
}
