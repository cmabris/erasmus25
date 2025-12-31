<?php

namespace App\Livewire\Admin\News;

use App\Models\AcademicYear;
use App\Models\NewsPost;
use App\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
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
    public string $filterProgram = '';

    /**
     * Filter by academic year.
     */
    #[Url(as: 'anio')]
    public string $filterAcademicYear = '';

    /**
     * Filter by status.
     */
    #[Url(as: 'estado')]
    public string $filterStatus = '';

    /**
     * Filter to show deleted news posts.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'created_at';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'desc';

    /**
     * Number of items per page.
     */
    #[Url(as: 'por-pagina')]
    public int $perPage = 15;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * News post ID to delete (for confirmation).
     */
    public ?int $newsPostToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * News post ID to restore (for confirmation).
     */
    public ?int $newsPostToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * News post ID to force delete (for confirmation).
     */
    public ?int $newsPostToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', NewsPost::class);
    }

    /**
     * Get paginated and filtered news posts.
     */
    #[Computed]
    public function newsPosts(): LengthAwarePaginator
    {
        return NewsPost::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filterProgram, fn ($query) => $query->where('program_id', $this->filterProgram))
            ->when($this->filterAcademicYear, fn ($query) => $query->where('academic_year_id', $this->filterAcademicYear))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('excerpt', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->with(['program', 'academicYear', 'author', 'tags'])
            ->withCount(['tags'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Get all programs for filter dropdown.
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Get all academic years for filter dropdown.
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::query()
            ->orderBy('year', 'desc')
            ->get();
    }

    /**
     * Sort by field.
     */
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }

        $this->resetPage();
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $newsPostId): void
    {
        $this->newsPostToDelete = $newsPostId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a news post (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->newsPostToDelete) {
            return;
        }

        $newsPost = NewsPost::findOrFail($this->newsPostToDelete);

        $this->authorize('delete', $newsPost);

        $newsPost->delete();

        $this->newsPostToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('news-post-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $newsPostId): void
    {
        $this->newsPostToRestore = $newsPostId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted news post.
     */
    public function restore(): void
    {
        if (! $this->newsPostToRestore) {
            return;
        }

        $newsPost = NewsPost::onlyTrashed()->findOrFail($this->newsPostToRestore);

        $this->authorize('restore', $newsPost);

        $newsPost->restore();

        $this->newsPostToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('news-post-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $newsPostId): void
    {
        $this->newsPostToForceDelete = $newsPostId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a news post (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->newsPostToForceDelete) {
            return;
        }

        $newsPost = NewsPost::onlyTrashed()->findOrFail($this->newsPostToForceDelete);

        $this->authorize('forceDelete', $newsPost);

        $newsPost->forceDelete();

        $this->newsPostToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('news-post-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Publish a news post.
     */
    public function publish(int $newsPostId): void
    {
        $newsPost = NewsPost::findOrFail($newsPostId);

        $this->authorize('publish', $newsPost);

        $newsPost->update([
            'status' => 'publicado',
            'published_at' => now(),
        ]);

        $this->resetPage();

        $this->dispatch('news-post-published', [
            'message' => __('Noticia publicada correctamente'),
        ]);
    }

    /**
     * Unpublish a news post.
     */
    public function unpublish(int $newsPostId): void
    {
        $newsPost = NewsPost::findOrFail($newsPostId);

        $this->authorize('publish', $newsPost);

        $newsPost->update([
            'status' => 'borrador',
            'published_at' => null,
        ]);

        $this->resetPage();

        $this->dispatch('news-post-unpublished', [
            'message' => __('Noticia despublicada correctamente'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterProgram', 'filterAcademicYear', 'filterStatus', 'showDeleted']);
        $this->showDeleted = '0'; // Reset to '0' (no)
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
    public function updatedFilterProgram(): void
    {
        $this->resetPage();
    }

    public function updatedFilterAcademicYear(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create news posts.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', NewsPost::class) ?? false;
    }

    /**
     * Check if user can view deleted news posts.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', NewsPost::class) ?? false;
    }

    /**
     * Check if user can delete a news post.
     */
    public function canDeleteNewsPost(NewsPost $newsPost): bool
    {
        return auth()->user()?->can('delete', $newsPost) ?? false;
    }

    /**
     * Check if user can publish a news post.
     */
    public function canPublishNewsPost(NewsPost $newsPost): bool
    {
        return auth()->user()?->can('publish', $newsPost) ?? false;
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'borrador' => 'neutral',
            'en_revision' => 'warning',
            'publicado' => 'success',
            'archivado' => 'neutral',
            default => 'neutral',
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.news.index')
            ->layout('components.layouts.app', [
                'title' => __('Noticias'),
            ]);
    }
}
