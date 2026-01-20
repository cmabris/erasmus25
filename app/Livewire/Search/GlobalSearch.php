<?php

namespace App\Livewire\Search;

use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Document;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class GlobalSearch extends Component
{
    /**
     * Search query term.
     */
    #[Url(as: 'q')]
    public string $query = '';

    /**
     * Types of content to search in.
     *
     * @var array<string>
     */
    #[Url(as: 'tipos')]
    public array $types = ['programs', 'calls', 'news', 'documents'];

    /**
     * Filter by program ID.
     */
    #[Url(as: 'programa')]
    public ?int $program = null;

    /**
     * Filter by academic year ID.
     */
    #[Url(as: 'ano')]
    public ?int $academicYear = null;

    /**
     * Show advanced filters panel.
     */
    public bool $showFilters = false;

    /**
     * Maximum number of results per type to show initially.
     */
    public int $limitPerType = 10;

    /**
     * Admin context flag (can be passed as parameter).
     */
    #[Url(as: 'admin')]
    public bool $admin = false;

    /**
     * Mount the component.
     */
    public function mount(?bool $admin = null): void
    {
        // Set admin context if provided
        if ($admin !== null) {
            $this->admin = $admin;
        }
    }

    /**
     * Check if we are in admin context.
     * Detects context from property, current route, or referer.
     */
    #[Computed]
    public function isAdminContext(): bool
    {
        // Check if admin flag is set
        if ($this->admin) {
            return true;
        }

        // Check if current route is admin
        if (request()->routeIs('admin.*')) {
            return true;
        }

        // Check referer if available (when navigating from admin)
        $referer = request()->header('referer');
        if ($referer && str_contains($referer, '/admin')) {
            return true;
        }

        return false;
    }

    /**
     * Get the route name for a program based on context.
     */
    public function getProgramRoute(Program $program): string
    {
        return $this->isAdminContext()
            ? route('admin.programs.show', $program)
            : route('programas.show', $program);
    }

    /**
     * Get the route name for a call based on context.
     */
    public function getCallRoute(Call $call): string
    {
        return $this->isAdminContext()
            ? route('admin.calls.show', $call)
            : route('convocatorias.show', $call);
    }

    /**
     * Get the route name for a news post based on context.
     */
    public function getNewsRoute(NewsPost $news): string
    {
        return $this->isAdminContext()
            ? route('admin.news.show', $news)
            : route('noticias.show', $news);
    }

    /**
     * Get the route name for a document based on context.
     */
    public function getDocumentRoute(Document $document): string
    {
        return $this->isAdminContext()
            ? route('admin.documents.show', $document)
            : route('documentos.show', $document);
    }

    /**
     * Available programs for filtering (cached).
     *
     * @return Collection<int, Program>
     */
    #[Computed]
    public function availablePrograms(): Collection
    {
        return Program::getCachedActive();
    }

    /**
     * Available academic years for filtering (cached).
     *
     * @return Collection<int, AcademicYear>
     */
    #[Computed]
    public function availableAcademicYears(): Collection
    {
        return AcademicYear::getCachedAll();
    }

    /**
     * Get search results grouped by type.
     *
     * @return array<string, Collection>
     */
    #[Computed]
    public function results(): array
    {
        if (empty($this->query) || empty($this->types)) {
            return [];
        }

        $results = [];

        if (in_array('programs', $this->types, true)) {
            $results['programs'] = $this->searchPrograms($this->query);
        }

        if (in_array('calls', $this->types, true)) {
            $results['calls'] = $this->searchCalls($this->query);
        }

        if (in_array('news', $this->types, true)) {
            $results['news'] = $this->searchNews($this->query);
        }

        if (in_array('documents', $this->types, true)) {
            $results['documents'] = $this->searchDocuments($this->query);
        }

        return $results;
    }

    /**
     * Get total number of results across all types.
     */
    #[Computed]
    public function totalResults(): int
    {
        return collect($this->results())
            ->sum(fn (Collection $collection) => $collection->count());
    }

    /**
     * Check if there are any results.
     */
    #[Computed]
    public function hasResults(): bool
    {
        return $this->totalResults() > 0;
    }

    /**
     * Search programs.
     *
     * @return Collection<int, Program>
     */
    protected function searchPrograms(string $query): Collection
    {
        return Program::query()
            ->where('is_active', true)
            ->when($this->program, fn ($q) => $q->where('id', $this->program))
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('code', 'like', "%{$query}%");
            })
            ->orderBy('order')
            ->orderBy('name')
            ->limit($this->limitPerType)
            ->get();
    }

    /**
     * Search calls.
     *
     * @return Collection<int, Call>
     */
    protected function searchCalls(string $query): Collection
    {
        return Call::query()
            ->with(['program', 'academicYear'])
            ->whereIn('status', ['abierta', 'cerrada'])
            ->whereNotNull('published_at')
            ->when($this->program, fn ($q) => $q->where('program_id', $this->program))
            ->when($this->academicYear, fn ($q) => $q->where('academic_year_id', $this->academicYear))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('requirements', 'like', "%{$query}%")
                    ->orWhere('documentation', 'like', "%{$query}%");
            })
            ->orderByRaw("CASE WHEN status = 'abierta' THEN 0 ELSE 1 END")
            ->orderBy('published_at', 'desc')
            ->limit($this->limitPerType)
            ->get();
    }

    /**
     * Search news posts.
     *
     * @return Collection<int, NewsPost>
     */
    protected function searchNews(string $query): Collection
    {
        return NewsPost::query()
            ->with(['program', 'academicYear', 'author', 'tags'])
            ->where('status', 'publicado')
            ->whereNotNull('published_at')
            ->when($this->program, fn ($q) => $q->where('program_id', $this->program))
            ->when($this->academicYear, fn ($q) => $q->where('academic_year_id', $this->academicYear))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderBy('published_at', 'desc')
            ->limit($this->limitPerType)
            ->get();
    }

    /**
     * Search documents.
     *
     * @return Collection<int, Document>
     */
    protected function searchDocuments(string $query): Collection
    {
        return Document::query()
            ->with(['category', 'program', 'academicYear', 'creator'])
            ->where('is_active', true)
            ->when($this->program, fn ($q) => $q->where('program_id', $this->program))
            ->when($this->academicYear, fn ($q) => $q->where('academic_year_id', $this->academicYear))
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->limit($this->limitPerType)
            ->get();
    }

    /**
     * Reset all filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['query', 'program', 'academicYear']);
        $this->types = ['programs', 'calls', 'news', 'documents'];
        $this->showFilters = false;
    }

    /**
     * Toggle a content type in the search.
     */
    public function toggleType(string $type): void
    {
        if (in_array($type, $this->types, true)) {
            $this->types = array_values(array_diff($this->types, [$type]));
        } else {
            $this->types[] = $type;
            $this->types = array_values(array_unique($this->types));
        }
    }

    /**
     * Toggle advanced filters panel.
     */
    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    /**
     * Handle search query changes.
     */
    public function updatedQuery(): void
    {
        // Reset filters when query changes significantly
        // This could be enhanced with debounce logic if needed
    }

    /**
     * Handle program filter changes.
     */
    public function updatedProgram(): void
    {
        // Could add logic here if needed
    }

    /**
     * Handle academic year filter changes.
     */
    public function updatedAcademicYear(): void
    {
        // Could add logic here if needed
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        $layout = $this->isAdminContext()
            ? 'components.layouts.app'
            : 'components.layouts.public';

        return view('livewire.search.global-search')
            ->layout($layout, [
                'title' => __('common.search.global_title').' - Erasmus+ Centro',
                'description' => __('common.search.global_description'),
            ]);
    }
}
