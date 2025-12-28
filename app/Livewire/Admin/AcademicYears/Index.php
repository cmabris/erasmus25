<?php

namespace App\Livewire\Admin\AcademicYears;

use App\Models\AcademicYear;
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
     * Search query for filtering academic years.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter to show deleted academic years.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'year';

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
     * Academic year ID to delete (for confirmation).
     */
    public ?int $academicYearToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Academic year ID to restore (for confirmation).
     */
    public ?int $academicYearToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Academic year ID to force delete (for confirmation).
     */
    public ?int $academicYearToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', AcademicYear::class);
    }

    /**
     * Get paginated and filtered academic years.
     */
    #[Computed]
    public function academicYears(): LengthAwarePaginator
    {
        return AcademicYear::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    // Optimize search: use exact match for year format (YYYY-YYYY) when possible
                    if (preg_match('/^\d{4}-\d{4}$/', $this->search)) {
                        $q->where('year', $this->search);
                    } else {
                        // Fallback to LIKE for partial matches
                        $q->where('year', 'like', "%{$this->search}%")
                            ->orWhere('start_date', 'like', "%{$this->search}%")
                            ->orWhere('end_date', 'like', "%{$this->search}%");
                    }
                });
            })
            ->withCount(['calls', 'newsPosts', 'documents'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('year', 'desc')
            ->paginate($this->perPage);
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
    public function confirmDelete(int $academicYearId): void
    {
        $this->academicYearToDelete = $academicYearId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete an academic year (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->academicYearToDelete) {
            return;
        }

        $academicYear = AcademicYear::findOrFail($this->academicYearToDelete);

        // Check if academic year has relationships
        $hasRelations = $academicYear->calls()->exists()
            || $academicYear->newsPosts()->exists()
            || $academicYear->documents()->exists();

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->academicYearToDelete = null;
            $this->dispatch('academic-year-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->authorize('delete', $academicYear);

        $academicYear->delete();

        $this->academicYearToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('academic-year-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $academicYearId): void
    {
        $this->academicYearToRestore = $academicYearId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted academic year.
     */
    public function restore(): void
    {
        if (! $this->academicYearToRestore) {
            return;
        }

        $academicYear = AcademicYear::onlyTrashed()->findOrFail($this->academicYearToRestore);

        $this->authorize('restore', $academicYear);

        $academicYear->restore();

        $this->academicYearToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('academic-year-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $academicYearId): void
    {
        $this->academicYearToForceDelete = $academicYearId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete an academic year (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->academicYearToForceDelete) {
            return;
        }

        $academicYear = AcademicYear::onlyTrashed()->findOrFail($this->academicYearToForceDelete);

        $this->authorize('forceDelete', $academicYear);

        // Verify no relations exist
        if ($academicYear->calls()->exists()
            || $academicYear->newsPosts()->exists()
            || $academicYear->documents()->exists()) {
            $this->showForceDeleteModal = false;
            $this->dispatch('academic-year-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $academicYear->forceDelete();

        $this->academicYearToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('academic-year-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'showDeleted']);
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
    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create academic years.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', AcademicYear::class) ?? false;
    }

    /**
     * Check if user can view deleted academic years.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', AcademicYear::class) ?? false;
    }

    /**
     * Check if an academic year can be deleted (has no relationships).
     */
    public function canDeleteAcademicYear(AcademicYear $academicYear): bool
    {
        if (! auth()->user()?->can('delete', $academicYear)) {
            return false;
        }

        // Check if it has relationships
        return ! ($academicYear->calls()->exists()
            || $academicYear->newsPosts()->exists()
            || $academicYear->documents()->exists());
    }

    /**
     * Toggle current status (mark/unmark as current academic year).
     */
    public function toggleCurrent(int $academicYearId): void
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);

        $this->authorize('update', $academicYear);

        if ($academicYear->is_current) {
            // Unmark as current
            $academicYear->unmarkAsCurrent();
            $message = __('Año académico desmarcado como actual correctamente');
        } else {
            // Mark as current - this will automatically unmark others
            $academicYear->markAsCurrent();
            $message = __('Año académico marcado como actual correctamente');
        }

        // Reset page to refresh the list
        $this->resetPage();

        $this->dispatch('academic-year-updated', [
            'message' => $message,
        ]);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.academic-years.index')
            ->layout('components.layouts.app', [
                'title' => __('Años Académicos'),
            ]);
    }
}
