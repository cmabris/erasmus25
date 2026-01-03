<?php

namespace App\Livewire\Admin\Documents;

use App\Http\Requests\UpdateDocumentRequest;
use App\Models\AcademicYear;
use App\Models\Document;
use App\Models\DocumentCategory;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\LivewireFilepond\WithFilePond;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;
    use WithFileUploads;

    /**
     * The document being edited.
     */
    public Document $document;

    /**
     * Category ID (required).
     */
    public ?int $categoryId = null;

    /**
     * Program ID (optional).
     */
    public ?int $programId = null;

    /**
     * Academic year ID (optional).
     */
    public ?int $academicYearId = null;

    /**
     * Document title.
     */
    public string $title = '';

    /**
     * Document slug (auto-generated from title).
     */
    public string $slug = '';

    /**
     * Document description.
     */
    public string $description = '';

    /**
     * Document type.
     */
    public string $documentType = 'otro';

    /**
     * Document version.
     */
    public string $version = '';

    /**
     * Whether the document is active.
     */
    public bool $isActive = true;

    /**
     * File to upload (new file to replace existing).
     */
    public ?UploadedFile $file = null;

    /**
     * Whether to remove existing file.
     */
    public bool $removeExistingFile = false;

    /**
     * Mount the component.
     */
    public function mount(Document $document): void
    {
        $this->authorize('update', $document);

        $this->document = $document->load(['category', 'program', 'academicYear', 'creator', 'updater']);

        // Pre-fill fields
        $this->categoryId = $document->category_id;
        $this->programId = $document->program_id;
        $this->academicYearId = $document->academic_year_id;
        $this->title = $document->title;
        $this->slug = $document->slug;
        $this->description = $document->description ?? '';
        $this->documentType = $document->document_type;
        $this->version = $document->version ?? '';
        $this->isActive = $document->is_active;
    }

    /**
     * Get all categories for select dropdown.
     */
    #[Computed]
    public function categories(): \Illuminate\Database\Eloquent\Collection
    {
        return DocumentCategory::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all programs for select dropdown.
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all academic years for select dropdown.
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::query()
            ->orderBy('year', 'desc')
            ->get();
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
     * Generate slug automatically from title when it changes.
     */
    public function updatedTitle(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->document->title)) {
            $this->slug = Str::slug($this->title);
        }
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => ['nullable', 'string', 'max:255', 'unique:documents,slug,'.$this->document->id],
        ]);
    }

    /**
     * Remove existing file.
     */
    public function removeFile(): void
    {
        $this->removeExistingFile = true;
        $this->file = null;
    }

    /**
     * Update the document.
     */
    public function update(): void
    {
        // Prepare data array for validation
        $data = [
            'category_id' => $this->categoryId,
            'program_id' => $this->programId,
            'academic_year_id' => $this->academicYearId,
            'title' => $this->title,
            'slug' => $this->slug ?: null,
            'description' => $this->description ?: null,
            'document_type' => $this->documentType,
            'version' => $this->version ?: null,
            'is_active' => $this->isActive,
            'file' => $this->file,
        ];

        // Validate using FormRequest rules
        $rules = (new UpdateDocumentRequest)->rules();
        $messages = (new UpdateDocumentRequest)->messages();

        $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();

        // Remove file from validated data as it's handled separately
        unset($validated['file']);

        // Set updated_by automatically to current user
        $validated['updated_by'] = auth()->id();

        // Update the document
        $this->document->update($validated);

        // Handle existing file
        if ($this->removeExistingFile) {
            $this->document->clearMediaCollection('file');
        }

        // Handle new file if uploaded
        if ($this->file) {
            // Remove existing file first
            $this->document->clearMediaCollection('file');

            // Add new file
            $this->document->addMedia($this->file->getRealPath())
                ->usingName($this->document->title)
                ->usingFileName($this->file->getClientOriginalName())
                ->toMediaCollection('file');
        }

        // Reload to get fresh data
        $this->document->refresh();

        $this->dispatch('document-updated', [
            'message' => __('El documento ":title" ha sido actualizado correctamente.', ['title' => $this->document->title]),
            'title' => __('Documento actualizado'),
        ]);

        $this->redirect(route('admin.documents.show', $this->document), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.documents.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Documento'),
            ]);
    }
}
