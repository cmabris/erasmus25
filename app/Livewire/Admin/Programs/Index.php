<?php

namespace App\Livewire\Admin\Programs;

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
     * Search query for filtering programs.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter to show only active programs.
     * Values: '' (all), '1' (active), '0' (inactive)
     */
    #[Url(as: 'activos')]
    public string $showActiveOnly = '';

    /**
     * Filter to show deleted programs.
     * Values: '0' (no), '1' (yes)
     */
    #[Url(as: 'eliminados')]
    public string $showDeleted = '0';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'order';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'asc';

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
     * Program ID to delete (for confirmation).
     */
    public ?int $programToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Program ID to restore (for confirmation).
     */
    public ?int $programToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Program ID to force delete (for confirmation).
     */
    public ?int $programToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Program::class);
    }

    /**
     * Get paginated and filtered programs.
     */
    #[Computed]
    public function programs(): LengthAwarePaginator
    {
        return Program::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->showActiveOnly !== '', function ($query) {
                $query->where('is_active', $this->showActiveOnly === '1');
            })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('code', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->withCount(['calls', 'newsPosts'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('name', 'asc')
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
     * Toggle active status of a program.
     */
    public function toggleActive(int $programId): void
    {
        $program = Program::withTrashed()->findOrFail($programId);

        $this->authorize('update', $program);

        $program->is_active = ! $program->is_active;
        $program->save();

        $this->dispatch('program-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $programId): void
    {
        $this->programToDelete = $programId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a program (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->programToDelete) {
            return;
        }

        $program = Program::findOrFail($this->programToDelete);

        // Check if program has relationships
        $hasRelations = $program->calls()->exists() || $program->newsPosts()->exists();
        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->programToDelete = null;
            $this->dispatch('program-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->authorize('delete', $program);

        $program->delete();

        $this->programToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('program-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $programId): void
    {
        $this->programToRestore = $programId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted program.
     */
    public function restore(): void
    {
        if (! $this->programToRestore) {
            return;
        }

        $program = Program::onlyTrashed()->findOrFail($this->programToRestore);

        $this->authorize('restore', $program);

        $program->restore();

        $this->programToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('program-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $programId): void
    {
        $this->programToForceDelete = $programId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a program (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->programToForceDelete) {
            return;
        }

        $program = Program::onlyTrashed()->findOrFail($this->programToForceDelete);

        $this->authorize('forceDelete', $program);

        // Verify no relations exist
        if ($program->calls()->exists() || $program->newsPosts()->exists()) {
            $this->showForceDeleteModal = false;
            $this->dispatch('program-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $program->forceDelete();

        $this->programToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('program-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'showActiveOnly', 'showDeleted']);
        $this->showActiveOnly = ''; // Reset to empty string (all)
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
    public function updatedShowActiveOnly(): void
    {
        $this->resetPage();
    }

    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create programs.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Program::class) ?? false;
    }

    /**
     * Check if user can view deleted programs.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', Program::class) ?? false;
    }

    /**
     * Check if a program can be deleted (has no relationships).
     */
    public function canDeleteProgram(Program $program): bool
    {
        return auth()->user()?->can('delete', $program) ?? false;
    }

    /**
     * Get all programs ordered for sorting operations.
     */
    private function getAllProgramsOrdered()
    {
        return Program::withTrashed()
            ->orderBy('order', 'asc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Move a program up in order (decrease order value).
     */
    public function moveUp(int $programId): void
    {
        $program = Program::withTrashed()->findOrFail($programId);
        $this->authorize('update', $program);

        $programs = $this->getAllProgramsOrdered();
        $currentIndex = $programs->search(fn ($p) => $p->id === $programId);

        if ($currentIndex === false || $currentIndex === 0) {
            return;
        }

        $previousProgram = $programs->get($currentIndex - 1);

        // Swap orders
        $tempOrder = $program->order;
        $program->order = $previousProgram->order;
        $previousProgram->order = $tempOrder;

        $program->save();
        $previousProgram->save();

        $this->dispatch('program-updated', [
            'message' => __('common.messages.order_updated_successfully'),
        ]);
    }

    /**
     * Move a program down in order (increase order value).
     */
    public function moveDown(int $programId): void
    {
        $program = Program::withTrashed()->findOrFail($programId);
        $this->authorize('update', $program);

        $programs = $this->getAllProgramsOrdered();
        $currentIndex = $programs->search(fn ($p) => $p->id === $programId);

        if ($currentIndex === false || $currentIndex === $programs->count() - 1) {
            return;
        }

        $nextProgram = $programs->get($currentIndex + 1);

        // Swap orders
        $tempOrder = $program->order;
        $program->order = $nextProgram->order;
        $nextProgram->order = $tempOrder;

        $program->save();
        $nextProgram->save();

        $this->dispatch('program-updated', [
            'message' => __('common.messages.order_updated_successfully'),
        ]);
    }

    /**
     * Check if a program can be moved up.
     */
    public function canMoveUp(int $programId): bool
    {
        $program = Program::withTrashed()->find($programId);
        if (! $program) {
            return false;
        }

        $programs = $this->getAllProgramsOrdered();
        $currentIndex = $programs->search(fn ($p) => $p->id === $programId);

        return $currentIndex !== false && $currentIndex > 0;
    }

    /**
     * Check if a program can be moved down.
     */
    public function canMoveDown(int $programId): bool
    {
        $program = Program::withTrashed()->find($programId);
        if (! $program) {
            return false;
        }

        $programs = $this->getAllProgramsOrdered();
        $currentIndex = $programs->search(fn ($p) => $p->id === $programId);

        return $currentIndex !== false && $currentIndex < $programs->count() - 1;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.programs.index')
            ->layout('components.layouts.app', [
                'title' => __('Programas Erasmus+'),
            ]);
    }
}
