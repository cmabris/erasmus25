<?php

namespace App\Livewire\Public\Documents;

use App\Models\Call;
use App\Models\Document;
use App\Models\MediaConsent;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Show extends Component
{
    /**
     * The document being displayed.
     */
    public Document $document;

    /**
     * Mount the component.
     */
    public function mount(Document $document): void
    {
        // Only show active documents
        if (! $document->is_active) {
            abort(404);
        }

        $this->document = $document->load(['category', 'program', 'academicYear', 'creator']);
    }

    /**
     * Get the file URL from Media Library.
     */
    #[Computed]
    public function fileUrl(): ?string
    {
        return $this->document->getFirstMediaUrl('file');
    }

    /**
     * Get the file size formatted.
     */
    #[Computed]
    public function fileSize(): ?string
    {
        $media = $this->document->getFirstMedia('file');

        if (! $media) {
            return null;
        }

        return $this->formatBytes($media->size);
    }

    /**
     * Get the file MIME type.
     */
    #[Computed]
    public function fileMimeType(): ?string
    {
        $media = $this->document->getFirstMedia('file');

        return $media?->mime_type;
    }

    /**
     * Get the file extension.
     */
    #[Computed]
    public function fileExtension(): ?string
    {
        $media = $this->document->getFirstMedia('file');

        if (! $media) {
            return null;
        }

        return pathinfo($media->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Get the file name.
     */
    #[Computed]
    public function fileName(): ?string
    {
        $media = $this->document->getFirstMedia('file');

        return $media?->file_name;
    }

    /**
     * Check if document has media consent.
     */
    #[Computed]
    public function hasMediaConsent(): bool
    {
        return MediaConsent::where('consent_document_id', $this->document->id)
            ->where('consent_given', true)
            ->whereNull('revoked_at')
            ->exists();
    }

    /**
     * Get media consents associated with this document.
     *
     * @return Collection<int, MediaConsent>
     */
    #[Computed]
    public function mediaConsents(): Collection
    {
        return MediaConsent::where('consent_document_id', $this->document->id)
            ->where('consent_given', true)
            ->whereNull('revoked_at')
            ->orderBy('consent_date', 'desc')
            ->get();
    }

    /**
     * Get related documents (same category or program, exclude current).
     *
     * @return Collection<int, Document>
     */
    #[Computed]
    public function relatedDocuments(): Collection
    {
        $query = Document::query()
            ->with(['category', 'program', 'academicYear'])
            ->where('id', '!=', $this->document->id)
            ->where('is_active', true);

        // If document has a category, prioritize same category
        if ($this->document->category_id) {
            $query->where('category_id', $this->document->category_id);
        } elseif ($this->document->program_id) {
            // If no category but has program, use program
            $query->where('program_id', $this->document->program_id);
        }

        return $query->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Get related calls from the same program (if applicable).
     *
     * @return Collection<int, Call>
     */
    #[Computed]
    public function relatedCalls(): Collection
    {
        if (! $this->document->program_id) {
            return collect();
        }

        return Call::query()
            ->with(['program', 'academicYear'])
            ->where('program_id', $this->document->program_id)
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Download the document file.
     */
    public function download(): BinaryFileResponse
    {
        $media = $this->document->getFirstMedia('file');

        if (! $media) {
            abort(404, __('Archivo no encontrado'));
        }

        // Increment download count
        $this->document->increment('download_count');

        // Return download response
        return response()->download(
            $media->getPath(),
            $media->file_name,
            [
                'Content-Type' => $media->mime_type,
            ]
        );
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, $precision).' '.$units[$i];
    }

    /**
     * Get document type configuration.
     */
    #[Computed]
    public function documentTypeConfig(): array
    {
        return match ($this->document->document_type) {
            'convocatoria' => ['icon' => 'document-text', 'color' => 'primary', 'label' => __('Convocatoria')],
            'modelo' => ['icon' => 'document-duplicate', 'color' => 'info', 'label' => __('Modelo')],
            'seguro' => ['icon' => 'shield-check', 'color' => 'success', 'label' => __('Seguro')],
            'consentimiento' => ['icon' => 'clipboard-document-check', 'color' => 'warning', 'label' => __('Consentimiento')],
            'guia' => ['icon' => 'book-open', 'color' => 'info', 'label' => __('Guía')],
            'faq' => ['icon' => 'question-mark-circle', 'color' => 'info', 'label' => __('FAQ')],
            'otro' => ['icon' => 'document', 'color' => 'neutral', 'label' => __('Otro')],
            default => ['icon' => 'document', 'color' => 'neutral', 'label' => __('Documento')],
        };
    }

    /**
     * Get the document preview image URL from Media Library.
     */
    #[Computed]
    public function previewImage(): ?string
    {
        return $this->document->getFirstMediaUrl('file', 'preview')
            ?: null;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $description = $this->document->description
            ? strip_tags($this->document->description)
            : __('Documento relacionado con programas Erasmus+.');

        return view('livewire.public.documents.show')
            ->layout('components.layouts.public', [
                'title' => $this->document->title.' - Documentos Erasmus+',
                'description' => $description,
                'image' => $this->previewImage,
            ]);
    }
}
