<?php

namespace App\Livewire\Admin\DocumentCategories;

use App\Http\Requests\StoreDocumentCategoryRequest;
use App\Models\DocumentCategory;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

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
    public function mount(): void
    {
        $this->authorize('create', DocumentCategory::class);
    }

    /**
     * Generate slug automatically from name when it changes.
     */
    public function updatedName(): void
    {
        if (empty($this->slug)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => ['nullable', 'string', 'max:255', 'unique:document_categories,slug'],
        ]);
    }

    /**
     * Store the document category.
     */
    public function store(): void
    {
        $validated = $this->validate((new StoreDocumentCategoryRequest)->rules());

        // Generate slug if not provided
        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        // Set order to 0 if not provided (database default)
        if (! isset($validated['order']) || $validated['order'] === null) {
            $validated['order'] = 0;
        }

        $documentCategory = DocumentCategory::create($validated);

        $this->dispatch('document-category-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.document-categories.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.document-categories.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Categoría'),
            ]);
    }
}
