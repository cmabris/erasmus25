<?php

namespace App\Livewire\Admin\Documents;

use App\Http\Requests\StoreDocumentRequest;
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

class Create extends Component
{
    use AuthorizesRequests;
    use WithFilePond;
    use WithFileUploads;

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
     * File to upload.
     */
    public ?UploadedFile $file = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', Document::class);
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
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->title);
        }
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => ['nullable', 'string', 'max:255', 'unique:documents,slug'],
        ]);
    }

    /**
     * Store the document.
     */
    public function store(): void
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
        $rules = (new StoreDocumentRequest)->rules();
        $messages = (new StoreDocumentRequest)->messages();

        try {
            $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Map validation errors from snake_case to camelCase component properties
            $errors = $e->errors();
            $mappedErrors = [];
            foreach ($errors as $key => $messages) {
                // Map snake_case keys to camelCase component properties
                $componentKey = match ($key) {
                    'category_id' => 'categoryId',
                    'program_id' => 'programId',
                    'academic_year_id' => 'academicYearId',
                    'document_type' => 'documentType',
                    'is_active' => 'isActive',
                    default => $key,
                };
                $mappedErrors[$componentKey] = $messages;
            }
            throw \Illuminate\Validation\ValidationException::withMessages($mappedErrors);
        }

        // Remove file from validated data as it's handled separately
        unset($validated['file']);

        // Set created_by automatically to current user
        $validated['created_by'] = auth()->id();

        // Create the document
        $document = Document::create($validated);

        // Handle file upload if exists
        if ($this->file) {
            $document->addMedia($this->file->getRealPath())
                ->usingName($document->title)
                ->usingFileName($this->file->getClientOriginalName())
                ->toMediaCollection('file');
        }

        $this->dispatch('document-created', [
            'message' => __('El documento ":title" ha sido creado correctamente.', ['title' => $document->title]),
            'title' => __('Documento creado'),
        ]);

        $this->redirect(route('admin.documents.show', $document), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.documents.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Documento'),
            ]);
    }
}
