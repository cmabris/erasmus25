<?php

namespace App\Livewire\Admin\NewsTags;

use App\Http\Requests\UpdateNewsTagRequest;
use App\Models\NewsTag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The news tag being edited.
     */
    public NewsTag $newsTag;

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
    public function mount(NewsTag $news_tag): void
    {
        $this->authorize('update', $news_tag);

        $this->newsTag = $news_tag;

        // Load news tag data
        $this->name = $news_tag->name;
        $this->slug = $news_tag->slug;
    }

    /**
     * Generate slug automatically from name when it changes.
     */
    public function updatedName(): void
    {
        if (empty($this->slug) || $this->slug === Str::slug($this->newsTag->name)) {
            $this->slug = Str::slug($this->name);
        }
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        $this->validateOnly('slug', [
            'slug' => ['nullable', 'string', 'max:255', 'unique:news_tags,slug,'.$this->newsTag->id],
        ]);
    }

    /**
     * Update the news tag.
     */
    public function update(): void
    {
        $validated = $this->validate((new UpdateNewsTagRequest)->rules());

        // Generate slug if not provided
        if (empty($validated['slug']) && ! empty($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $this->newsTag->update($validated);

        $this->dispatch('news-tag-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.news-tags.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news-tags.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Etiqueta'),
            ]);
    }
}
