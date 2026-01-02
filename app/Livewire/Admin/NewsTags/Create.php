<?php

namespace App\Livewire\Admin\NewsTags;

use App\Http\Requests\StoreNewsTagRequest;
use App\Models\NewsTag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * Tag name.
     */
    public string $name = '';

    /**
     * Tag slug.
     */
    public string $slug = '';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('create', NewsTag::class);
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
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_tags,slug'],
        ]);
    }

    /**
     * Store the news tag.
     */
    public function store(): void
    {
        $validated = $this->validate((new StoreNewsTagRequest)->rules());

        // Generate slug if not provided
        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $newsTag = NewsTag::create($validated);

        $this->dispatch('news-tag-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.news-tags.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news-tags.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Etiqueta'),
            ]);
    }
}
