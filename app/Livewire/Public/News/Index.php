<?php

namespace App\Livewire\Public\News;

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\NewsTag;
use App\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    /**
     * Search query for filtering news posts.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by program.
     */
    #[Url(as: 'programa')]
    public string $program = '';

    /**
     * Filter by academic year.
     */
    #[Url(as: 'ano')]
    public string $academicYear = '';

    /**
     * Filter by tags (comma-separated tag IDs).
     */
    #[Url(as: 'etiquetas')]
    public string $tags = '';

    /**
     * Available programs for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::where('is_active', true)
            ->orderBy('order')
            ->orderBy('name')
            ->get();
    }

    /**
     * Available academic years for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, AcademicYear>
     */
    #[Computed]
    public function availableAcademicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::orderBy('year', 'desc')
            ->get();
    }

    /**
     * Available tags for filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, NewsTag>
     */
    #[Computed]
    public function availableTags(): \Illuminate\Database\Eloquent\Collection
    {
        return NewsTag::orderBy('name')
            ->get();
    }

    /**
     * Get selected tag IDs from comma-separated string.
     *
     * @return array<int>
     */
    #[Computed]
    public function selectedTagIds(): array
    {
        if (empty($this->tags)) {
            return [];
        }

        return array_filter(
            array_map('intval', explode(',', $this->tags)),
            fn ($id) => $id > 0
        );
    }

    /**
     * Statistics for the news section.
     *
     * @return array<string, int>
     */
    #[Computed]
    public function stats(): array
    {
        $baseQuery = NewsPost::where('status', 'publicado')
            ->whereNotNull('published_at');

        return [
            'total' => (clone $baseQuery)->count(),
            'this_month' => (clone $baseQuery)
                ->whereMonth('published_at', now()->month)
                ->whereYear('published_at', now()->year)
                ->count(),
            'this_year' => (clone $baseQuery)
                ->whereYear('published_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get paginated and filtered news posts.
     */
    #[Computed]
    public function news(): LengthAwarePaginator
    {
        $tagIds = $this->selectedTagIds();

        return NewsPost::query()
            ->with(['program', 'academicYear', 'author', 'tags'])
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('excerpt', 'like', "%{$this->search}%")
                        ->orWhere('content', 'like', "%{$this->search}%");
                });
            })
            ->when($this->program, fn ($query) => $query->where('program_id', $this->program))
            ->when($this->academicYear, fn ($query) => $query->where('academic_year_id', $this->academicYear))
            ->when(! empty($tagIds), function ($query) use ($tagIds) {
                $query->whereHas('tags', fn ($q) => $q->whereIn('news_tags.id', $tagIds));
            })
            ->orderBy('published_at', 'desc')
            ->paginate(12);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'program', 'academicYear', 'tags']);
        $this->resetPage();
    }

    /**
     * Handle search input changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Handle filter changes.
     */
    public function updatedProgram(): void
    {
        $this->resetPage();
    }

    public function updatedAcademicYear(): void
    {
        $this->resetPage();
    }

    public function updatedTags(): void
    {
        $this->resetPage();
    }

    /**
     * Toggle a tag in the filter.
     */
    public function toggleTag(int $tagId): void
    {
        $tagIds = $this->selectedTagIds();

        if (in_array($tagId, $tagIds, true)) {
            $tagIds = array_values(array_diff($tagIds, [$tagId]));
        } else {
            $tagIds[] = $tagId;
        }

        $this->tags = ! empty($tagIds) ? implode(',', $tagIds) : '';
        $this->resetPage();
    }

    /**
     * Remove a tag from the filter.
     */
    public function removeTag(int $tagId): void
    {
        $tagIds = $this->selectedTagIds();
        $tagIds = array_values(array_diff($tagIds, [$tagId]));
        $this->tags = ! empty($tagIds) ? implode(',', $tagIds) : '';
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.public.news.index')
            ->layout('components.layouts.public', [
                'title' => __('Noticias Erasmus+ - Centro de Movilidad Internacional'),
                'description' => __('Descubre las últimas noticias, experiencias y novedades sobre movilidad internacional Erasmus+. Testimonios, proyectos y oportunidades de formación.'),
            ]);
    }
}
