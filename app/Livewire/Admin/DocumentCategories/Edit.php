<?php

namespace App\Livewire\Admin\DocumentCategories;

use App\Http\Requests\UpdateDocumentCategoryRequest;
use App\Models\DocumentCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The document category being edited.
     */
    public DocumentCategory $documentCategory;

    /**
     * Category name.
     */
    public string $name = '';

    /**
     * Category slug.
     */
    public string $slug = '';

    /**
     * Category description.
     */
    public ?string $description = null;

    /**
     * Category order.
     */
    public ?int $order = null;

    /**
     * Mount the component.
     */
    public function mount(DocumentCategory $document_category): void
    {
        $this->authorize('update', $document_category);

        $this->documentCategory = $document_category;

        // Load document category data
        $this->name = $document_category->name;
        $this->slug = $document_category->slug;
        $this->description = $document_category->description;
        $this->order = $document_category->order;
    }

    /**
     * Generate slug automatically from name when it changes.
     */
    public function updatedName(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->documentCategory->name)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => ['nullable', 'string', 'max:255', 'unique:document_categories,slug,'.$this->documentCategory->id],
        ]);
    }

    /**
     * Update the document category.
     */
    public function update(): void
    {
        // IMPORTANTE: Construir manualmente el array de datos para asegurar que todos los campos estén presentes
        // Si un campo no fue enviado por Livewire (porque no fue modificado), usar el valor del modelo
        $name = trim($this->name ?? '');
        if ($name === '') {
            $name = $this->documentCategory->name;
        }

        $slug = trim($this->slug ?? '');
        if ($slug === '') {
            $slug = $this->documentCategory->slug ?: Str::slug($name);
        }

        // Construir el array de datos completo
        $data = [
            'name' => $name,
            'slug' => $slug ?: null,
            'description' => $this->description ?: null,
            'order' => $this->order,
        ];

        // Get rules and messages from FormRequest
        $rules = (new UpdateDocumentCategoryRequest)->rules();
        $messages = (new UpdateDocumentCategoryRequest)->messages();

        // Fix validation: UpdateDocumentCategoryRequest tries to get ID from route, but in Livewire we need to pass it manually
        // Both name and slug need to ignore the current document category ID
        $rules['name'] = ['required', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'name')->ignore($this->documentCategory->id)];
        $rules['slug'] = ['nullable', 'string', 'max:255', \Illuminate\Validation\Rule::unique('document_categories', 'slug')->ignore($this->documentCategory->id)];

        // Validate using Validator::make() directly to ensure all fields are validated
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            // Añadir los errores manualmente al componente para que Livewire los muestre
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        $validated = $validator->validated();

        $this->documentCategory->update($validated);

        $this->dispatch('document-category-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.document-categories.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.document-categories.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Categoría'),
            ]);
    }
}
