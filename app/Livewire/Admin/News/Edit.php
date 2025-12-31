<?php

namespace App\Livewire\Admin\News;

use App\Http\Requests\StoreNewsTagRequest;
use App\Http\Requests\UpdateNewsPostRequest;
use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Spatie\LivewireFilepond\WithFilePond;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

    /**
     * The news post being edited.
     */
    public NewsPost $newsPost;

    /**
     * Program ID (optional).
     */
    public int $program_id = 0;

    /**
     * Academic year ID (required).
     */
    public int $academic_year_id = 0;

    /**
     * Title.
     */
    public string $title = '';

    /**
     * Slug (auto-generated from title).
     */
    public string $slug = '';

    /**
     * Excerpt.
     */
    public string $excerpt = '';

    /**
     * Content.
     */
    public string $content = '';

    /**
     * Country.
     */
    public string $country = '';

    /**
     * City.
     */
    public string $city = '';

    /**
     * Host entity.
     */
    public string $host_entity = '';

    /**
     * Mobility type (alumnado/personal).
     */
    public string $mobility_type = '';

    /**
     * Mobility category.
     */
    public string $mobility_category = '';

    /**
     * Status.
     */
    public string $status = 'borrador';

    /**
     * Published at date.
     */
    public string $published_at = '';

    /**
     * Selected tags (array of tag IDs).
     *
     * @var array<int>
     */
    public array $selectedTags = [];

    /**
     * Featured image (new upload).
     */
    public ?UploadedFile $featuredImage = null;

    /**
     * URL of existing featured image.
     */
    public ?string $featuredImageUrl = null;

    /**
     * Whether to remove existing image.
     */
    public bool $removeFeaturedImage = false;

    /**
     * Show delete modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Show create tag modal.
     */
    public bool $showCreateTagModal = false;

    /**
     * New tag name (for creating tag).
     */
    public string $newTagName = '';

    /**
     * New tag slug (for creating tag).
     */
    public string $newTagSlug = '';

    /**
     * Tag search filter.
     */
    public string $tagSearch = '';

    /**
     * Mount the component.
     */
    public function mount(NewsPost $news_post): void
    {
        $this->authorize('update', $news_post);

        $this->newsPost = $news_post;

        // Load news post data
        $this->program_id = $news_post->program_id ?? 0;
        $this->academic_year_id = $news_post->academic_year_id ?? 0;
        $this->title = $news_post->title;
        $this->slug = $news_post->slug ?? '';
        $this->excerpt = $news_post->excerpt ?? '';
        $this->content = $news_post->content ?? '';
        $this->country = $news_post->country ?? '';
        $this->city = $news_post->city ?? '';
        $this->host_entity = $news_post->host_entity ?? '';
        $this->mobility_type = $news_post->mobility_type ?? '';
        $this->mobility_category = $news_post->mobility_category ?? '';
        $this->status = $news_post->status;
        $this->published_at = $news_post->published_at?->format('Y-m-d\TH:i') ?? '';

        // Load selected tags
        $this->selectedTags = $news_post->tags->pluck('id')->toArray();

        // Load existing featured image URL
        $this->featuredImageUrl = $news_post->getFirstMediaUrl('featured');
    }

    /**
     * Get all programs for dropdown.
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all academic years for dropdown.
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::query()
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Get all available tags (filtered by search).
     */
    #[Computed]
    public function availableTags(): \Illuminate\Database\Eloquent\Collection
    {
        return NewsTag::query()
            ->when($this->tagSearch, fn ($query) => $query->where('name', 'like', "%{$this->tagSearch}%"))
            ->orderBy('name')
            ->get();
    }

    /**
     * Generate slug from title when title changes.
     */
    public function updatedTitle(): void
    {
        if ($this->title && (empty($this->slug) || $this->slug === Str::slug($this->newsPost->title))) {
            $this->slug = Str::slug($this->title);
        }

        // Validate title in real-time
        $this->validateOnly('title', [
            'title' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Validate slug when it changes.
     */
    public function updatedSlug(): void
    {
        if ($this->slug) {
            $this->validateOnly('slug', [
                'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug,'.$this->newsPost->id],
            ]);
        }
    }

    /**
     * Update featured image validation when image changes.
     */
    public function updatedFeaturedImage(): void
    {
        if ($this->featuredImage) {
            $this->validate([
                'featuredImage' => ['image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'], // 5MB max
            ]);
            $this->removeFeaturedImage = false; // If uploading new image, don't remove existing
        }
    }

    /**
     * Toggle removal of existing image.
     */
    public function toggleRemoveFeaturedImage(): void
    {
        $this->removeFeaturedImage = ! $this->removeFeaturedImage;
        if ($this->removeFeaturedImage) {
            $this->featuredImage = null;
        }
    }

    /**
     * Check if news post has existing featured image.
     */
    public function hasExistingFeaturedImage(): bool
    {
        return $this->newsPost->hasMedia('featured');
    }

    /**
     * Create a new tag.
     */
    public function createTag(): void
    {
        $this->authorize('create', NewsTag::class);

        $validated = $this->validate((new StoreNewsTagRequest)->rules());

        $tag = NewsTag::create($validated);

        // Add the new tag to selected tags
        $this->selectedTags[] = $tag->id;

        // Reset form
        $this->newTagName = '';
        $this->newTagSlug = '';
        $this->showCreateTagModal = false;

        $this->dispatch('tag-created', [
            'message' => __('Etiqueta creada correctamente'),
        ]);
    }

    /**
     * Generate slug from tag name when name changes.
     */
    public function updatedNewTagName(): void
    {
        if ($this->newTagName && ! $this->newTagSlug) {
            $this->newTagSlug = Str::slug($this->newTagName);
        }
    }

    /**
     * Update the news post.
     */
    public function update(): void
    {
        // Get rules from FormRequest and exclude fields not in component
        $rules = (new UpdateNewsPostRequest)->rules();
        unset($rules['author_id'], $rules['reviewed_by'], $rules['reviewed_at'], $rules['featured_image'], $rules['tags'], $rules['tags.*']);

        $validated = $this->validate($rules);

        // Convert program_id and academic_year_id from 0 to null if needed
        if ($validated['program_id'] === 0) {
            $validated['program_id'] = null;
        }
        if ($validated['academic_year_id'] === 0) {
            $validated['academic_year_id'] = null;
        }

        // Convert empty strings to null for optional fields
        $optionalFields = ['country', 'city', 'host_entity', 'mobility_type', 'mobility_category', 'excerpt'];
        foreach ($optionalFields as $field) {
            if (isset($validated[$field]) && $validated[$field] === '') {
                $validated[$field] = null;
            }
        }

        // Handle published_at
        if (isset($validated['published_at']) && $validated['published_at'] === '') {
            $validated['published_at'] = null;
        }

        $this->newsPost->update($validated);

        // Validate and sync tags
        if (! empty($this->selectedTags)) {
            $this->validate([
                'selectedTags' => ['array'],
                'selectedTags.*' => ['required', 'exists:news_tags,id'],
            ]);
        }
        $this->newsPost->tags()->sync($this->selectedTags ?? []);

        // Handle featured image removal
        if ($this->removeFeaturedImage && $this->newsPost->hasMedia('featured')) {
            $this->newsPost->clearMediaCollection('featured');
            $this->featuredImageUrl = null;
        }

        // Handle new featured image upload
        if ($this->featuredImage) {
            // Remove existing image if uploading new one
            if ($this->newsPost->hasMedia('featured')) {
                $this->newsPost->clearMediaCollection('featured');
            }

            $this->newsPost->addMedia($this->featuredImage->getRealPath())
                ->usingName($this->newsPost->title)
                ->usingFileName($this->featuredImage->getClientOriginalName())
                ->toMediaCollection('featured');

            // Update URL for preview
            $this->featuredImageUrl = $this->newsPost->getFirstMediaUrl('featured');
        }

        $this->dispatch('news-post-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.news.show', $this->newsPost), navigate: true);
    }

    /**
     * Delete the news post (soft delete).
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->newsPost);

        $this->newsPost->delete();

        $this->dispatch('news-post-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.news.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Noticia'),
            ]);
    }
}
