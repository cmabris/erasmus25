<?php

namespace App\Livewire\Admin\Documents;

use App\Models\Document;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The document being displayed.
     */
    public Document $document;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Document $document): void
    {
        $this->authorize('view', $document);

        // Load relationships with eager loading to avoid N+1 queries
        $this->document = $document->load([
            'category' => fn ($query) => $query->select('id', 'name', 'slug'),
            'program' => fn ($query) => $query->select('id', 'name', 'slug'),
            'academicYear' => fn ($query) => $query->select('id', 'year', 'start_date', 'end_date'),
            'creator' => fn ($query) => $query->select('id', 'name', 'email'),
            'updater' => fn ($query) => $query->select('id', 'name', 'email'),
            'mediaConsents' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['mediaConsents']);
    }

    /**
     * Get existing file media.
     */
    #[Computed]
    public function existingFile()
    {
        return $this->document->getFirstMedia('file');
    }

    /**
     * Check if document has file.
     */
    public function hasFile(): bool
    {
        return $this->document->hasMedia('file');
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
     * Get document type badge color.
     */
    public function getDocumentTypeColor(string $type): string
    {
        return match ($type) {
            'convocatoria' => 'blue',
            'modelo' => 'purple',
            'seguro' => 'orange',
            'consentimiento' => 'yellow',
            'guia' => 'green',
            'faq' => 'cyan',
            'otro' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Delete the document (soft delete).
     */
    public function delete(): void
    {
        // Check if document has relationships using the loaded count
        $hasRelations = ($this->document->media_consents_count ?? 0) > 0;

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('document-delete-error', [
                'message' => __('No se puede eliminar el documento porque tiene consentimientos de medios asociados.'),
            ]);

            return;
        }

        $this->authorize('delete', $this->document);

        $documentTitle = $this->document->title;
        $this->document->delete();

        $this->dispatch('document-deleted', [
            'message' => __('El documento ":title" ha sido eliminado correctamente. Puede restaurarlo desde la sección de eliminados.', ['title' => $documentTitle]),
            'title' => __('Documento eliminado'),
        ]);

        $this->redirect(route('admin.documents.index'), navigate: true);
    }

    /**
     * Restore the document.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->document);

        $this->document->restore();

        // Reload the document to refresh the view
        $this->document->refresh();

        $this->dispatch('document-restored', [
            'message' => __('El documento ":title" ha sido restaurado correctamente.', ['title' => $this->document->title]),
            'title' => __('Documento restaurado'),
        ]);
    }

    /**
     * Permanently delete the document.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->document);

        // Check relations one more time using the loaded count
        $hasRelations = ($this->document->media_consents_count ?? 0) > 0;

        if ($hasRelations) {
            $this->showForceDeleteModal = false;
            $this->dispatch('document-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente el documento porque tiene consentimientos de medios asociados.'),
            ]);

            return;
        }

        $documentTitle = $this->document->title;
        $this->document->forceDelete();

        $this->dispatch('document-force-deleted', [
            'message' => __('El documento ":title" ha sido eliminado permanentemente del sistema. Esta acción no se puede revertir.', ['title' => $documentTitle]),
            'title' => __('Documento eliminado permanentemente'),
        ]);

        $this->redirect(route('admin.documents.index'), navigate: true);
    }

    /**
     * Check if the document can be deleted (has no relationships).
     * Uses the loaded count to avoid additional queries.
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->document)) {
            return false;
        }

        // Check if it has relationships using the loaded count
        return ($this->document->media_consents_count ?? 0) === 0;
    }

    /**
     * Check if the document has relationships.
     * Uses the loaded count to avoid additional queries.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return ($this->document->media_consents_count ?? 0) > 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.documents.show')
            ->layout('components.layouts.app', [
                'title' => $this->document->title ?? 'Documento',
            ]);
    }
}
