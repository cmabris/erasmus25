<?php

namespace App\Livewire\Admin\News;

use App\Http\Requests\StoreNewsPostRequest;
use App\Http\Requests\StoreNewsTagRequest;
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

class Create extends Component
{
    use AuthorizesRequests;
    use WithFilePond;

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
     * Featured image.
     */
    public ?UploadedFile $featuredImage = null;

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
    public function mount(): void
    {
        $this->authorize('create', NewsPost::class);
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
        if ($this->title && ! $this->slug) {
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
                'slug' => ['nullable', 'string', 'max:255', 'unique:news_posts,slug'],
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
        }
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
     * Store the news post.
     */
    public function store(): void
    {
        // Get rules from FormRequest and exclude fields not in component
        $rules = (new StoreNewsPostRequest)->rules();
        unset($rules['author_id'], $rules['reviewed_by'], $rules['reviewed_at'], $rules['featured_image'], $rules['tags'], $rules['tags.*']);

        $validated = $this->validate($rules);

        // Set author_id automatically to current user
        $validated['author_id'] = auth()->id();

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

        $newsPost = NewsPost::create($validated);

        // Validate and sync tags
        if (! empty($this->selectedTags)) {
            $this->validate([
                'selectedTags' => ['array'],
                'selectedTags.*' => ['required', 'exists:news_tags,id'],
            ]);
        }
        $newsPost->tags()->sync($this->selectedTags ?? []);

        // Handle featured image upload
        if ($this->featuredImage) {
            $newsPost->addMedia($this->featuredImage->getRealPath())
                ->usingName($newsPost->title)
                ->usingFileName($this->featuredImage->getClientOriginalName())
                ->toMediaCollection('featured');
        }

        $this->dispatch('news-post-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.news.show', $newsPost), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Noticia'),
            ]);
    }
}
