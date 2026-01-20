<?php

namespace App\Livewire\Public\News;

use App\Models\Call;
use App\Models\NewsPost;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    /**
     * The news post being displayed.
     */
    public NewsPost $newsPost;

    /**
     * Mount the component.
     */
    public function mount(NewsPost $newsPost): void
    {
        // Only show published news posts
        if ($newsPost->status !== 'publicado' || ! $newsPost->published_at) {
            abort(404);
        }

        // Eager load relationships to avoid N+1 queries
        $this->newsPost = $newsPost->load(['program', 'academicYear', 'author', 'tags', 'media']);
    }

    /**
     * Get the featured image URL from Media Library.
     */
    #[Computed]
    public function featuredImage(): ?string
    {
        return $this->newsPost->getFirstMediaUrl('featured');
    }

    /**
     * Get related news posts (same program, different tags, exclude current).
     *
     * @return Collection<int, NewsPost>
     */
    #[Computed]
    public function relatedNews(): Collection
    {
        $query = NewsPost::query()
            ->with(['program', 'academicYear', 'author', 'tags'])
            ->where('id', '!=', $this->newsPost->id)
            ->where('status', 'publicado')
            ->whereNotNull('published_at');

        // If news post has a program, prioritize same program
        if ($this->newsPost->program_id) {
            $query->where('program_id', $this->newsPost->program_id);
        }

        // If news post has tags, prioritize news with at least one common tag
        if ($this->newsPost->tags->isNotEmpty()) {
            $tagIds = $this->newsPost->tags->pluck('id')->toArray();
            $query->whereHas('tags', fn ($q) => $q->whereIn('news_tags.id', $tagIds));
        }

        return $query->orderBy('published_at', 'desc')
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
        if (! $this->newsPost->program_id) {
            return collect();
        }

        return Call::query()
            ->with(['program', 'academicYear'])
            ->where('program_id', $this->newsPost->program_id)
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $excerpt = $this->newsPost->excerpt
            ? Str::limit(strip_tags($this->newsPost->excerpt), 160)
            : __('Noticia sobre movilidad internacional Erasmus+.');

        return view('livewire.public.news.show')
            ->layout('components.layouts.public', [
                'title' => $this->newsPost->title.' - Noticias Erasmus+',
                'description' => $excerpt,
            ]);
    }
}
