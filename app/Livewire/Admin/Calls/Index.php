<?php

namespace App\Livewire\Admin\Calls;

use App\Exports\CallsExport;
use App\Models\AcademicYear;
use App\Models\Call;
use App\Models\Program;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * Search query for filtering calls.
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
     * Filter by type (alumnado/personal).
     */
    #[Url(as: 'tipo')]
    public string $filterType = '';

    /**
     * Filter by modality (corta/larga).
     */
    #[Url(as: 'modalidad')]
    public string $filterModality = '';

    /**
     * Filter by status.
     */
    #[Url(as: 'estado')]
    public string $filterStatus = '';

    /**
     * Filter to show deleted calls.
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
     * Call ID to delete (for confirmation).
     */
    public ?int $callToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Call ID to restore (for confirmation).
     */
    public ?int $callToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Call ID to force delete (for confirmation).
     */
    public ?int $callToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Call::class);
    }

    /**
     * Get paginated and filtered calls.
     */
    #[Computed]
    public function calls(): LengthAwarePaginator
    {
        return Call::query()
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filterProgram, fn ($query) => $query->where('program_id', $this->filterProgram))
            ->when($this->filterAcademicYear, fn ($query) => $query->where('academic_year_id', $this->filterAcademicYear))
            ->when($this->filterType, fn ($query) => $query->where('type', $this->filterType))
            ->when($this->filterModality, fn ($query) => $query->where('modality', $this->filterModality))
            ->when($this->filterStatus, fn ($query) => $query->where('status', $this->filterStatus))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', "%{$this->search}%")
                        ->orWhere('slug', 'like', "%{$this->search}%");
                });
            })
            ->with(['program', 'academicYear', 'creator', 'updater'])
            ->withCount(['phases', 'resolutions', 'applications'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Get all programs for filter dropdown (cached).
     */
    #[Computed]
    public function programs(): \Illuminate\Database\Eloquent\Collection
    {
        return Program::getCachedActive();
    }

    /**
     * Get all academic years for filter dropdown (cached).
     */
    #[Computed]
    public function academicYears(): \Illuminate\Database\Eloquent\Collection
    {
        return AcademicYear::getCachedAll();
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
     * Change call status.
     */
    public function changeStatus(int $callId, string $status): void
    {
        $call = Call::withTrashed()->findOrFail($callId);

        $this->authorize('update', $call);

        $oldStatus = $call->status;
        $call->status = $status;

        // Update published_at if publishing
        if ($status === 'abierta' && ! $call->published_at) {
            $call->published_at = now();
        }

        // Update closed_at if closing
        if ($status === 'cerrada' && ! $call->closed_at) {
            $call->closed_at = now();
        }

        $call->updated_by = auth()->id();
        $call->save();

        // Get status label for message
        $statusLabels = [
            'borrador' => __('Borrador'),
            'abierta' => __('Abierta'),
            'cerrada' => __('Cerrada'),
            'en_baremacion' => __('En Baremación'),
            'resuelta' => __('Resuelta'),
            'archivada' => __('Archivada'),
        ];

        $this->dispatch('call-updated', [
            'message' => __('Estado de la convocatoria cambiado de :old a :new', [
                'old' => $statusLabels[$oldStatus] ?? $oldStatus,
                'new' => $statusLabels[$status] ?? $status,
            ]),
            'title' => __('Estado actualizado'),
        ]);
    }

    /**
     * Publish call.
     */
    public function publish(int $callId): void
    {
        $call = Call::findOrFail($callId);

        $this->authorize('publish', $call);

        $call->status = 'abierta';
        $call->published_at = now();
        $call->updated_by = auth()->id();
        $call->save();

        $this->dispatch('call-published', [
            'message' => __('Convocatoria publicada correctamente. La convocatoria está ahora abierta y visible públicamente.'),
            'title' => __('Convocatoria publicada'),
        ]);
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $callId): void
    {
        $this->callToDelete = $callId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a call (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->callToDelete) {
            return;
        }

        // Optimize: Load call with counts in a single query
        $call = Call::withCount(['phases', 'resolutions', 'applications'])
            ->findOrFail($this->callToDelete);

        // Check if call has relationships using loaded counts (more efficient)
        $hasRelations = ($call->phases_count > 0)
            || ($call->resolutions_count > 0)
            || ($call->applications_count > 0);

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->callToDelete = null;
            $this->dispatch('call-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
                'details' => __('No se puede eliminar esta convocatoria porque tiene :phases fases, :resolutions resoluciones y :applications aplicaciones asociadas.', [
                    'phases' => $call->phases_count,
                    'resolutions' => $call->resolutions_count,
                    'applications' => $call->applications_count,
                ]),
            ]);

            return;
        }

        $this->authorize('delete', $call);

        $call->delete();

        $this->callToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('call-deleted', [
            'message' => __('common.messages.deleted_successfully'),
            'title' => __('Convocatoria eliminada'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $callId): void
    {
        $this->callToRestore = $callId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted call.
     */
    public function restore(): void
    {
        if (! $this->callToRestore) {
            return;
        }

        $call = Call::onlyTrashed()->findOrFail($this->callToRestore);

        $this->authorize('restore', $call);

        $call->restore();
        $call->updated_by = auth()->id();
        $call->save();

        $this->callToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('call-restored', [
            'message' => __('common.messages.restored_successfully'),
            'title' => __('Convocatoria restaurada'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $callId): void
    {
        $this->callToForceDelete = $callId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a call (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->callToForceDelete) {
            return;
        }

        // Optimize: Load call with counts in a single query
        $call = Call::onlyTrashed()
            ->withCount(['phases', 'resolutions', 'applications'])
            ->findOrFail($this->callToForceDelete);

        $this->authorize('forceDelete', $call);

        // Verify no relations exist using loaded counts (more efficient)
        if (($call->phases_count > 0)
            || ($call->resolutions_count > 0)
            || ($call->applications_count > 0)) {
            $this->showForceDeleteModal = false;
            $this->dispatch('call-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
                'details' => __('No se puede eliminar permanentemente esta convocatoria porque tiene :phases fases, :resolutions resoluciones y :applications aplicaciones asociadas.', [
                    'phases' => $call->phases_count,
                    'resolutions' => $call->resolutions_count,
                    'applications' => $call->applications_count,
                ]),
            ]);

            return;
        }

        $call->forceDelete();

        $this->callToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $this->dispatch('call-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
            'title' => __('Convocatoria eliminada permanentemente'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterProgram', 'filterAcademicYear', 'filterType', 'filterModality', 'filterStatus', 'showDeleted']);
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

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterModality(): void
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
     * Check if user can create calls.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Call::class) ?? false;
    }

    /**
     * Check if user can view deleted calls.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', Call::class) ?? false;
    }

    /**
     * Export calls to Excel.
     */
    public function export()
    {
        $this->authorize('viewAny', Call::class);

        $filters = [
            'search' => $this->search,
            'filterProgram' => $this->filterProgram,
            'filterAcademicYear' => $this->filterAcademicYear,
            'filterType' => $this->filterType,
            'filterModality' => $this->filterModality,
            'filterStatus' => $this->filterStatus,
            'showDeleted' => $this->showDeleted,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $filename = 'convocatorias-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new CallsExport($filters), $filename);
    }

    /**
     * Get status badge color.
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'borrador' => 'gray',
            'abierta' => 'green',
            'cerrada' => 'yellow',
            'en_baremacion' => 'blue',
            'resuelta' => 'purple',
            'archivada' => 'zinc',
            default => 'gray',
        };
    }

    /**
     * Get valid status transitions for a given status.
     */
    public function getValidStatusTransitions(string $currentStatus): array
    {
        $transitions = [
            'borrador' => ['abierta', 'en_baremacion'],
            'abierta' => ['cerrada', 'en_baremacion'],
            'cerrada' => ['en_baremacion', 'archivada'],
            'en_baremacion' => ['resuelta', 'abierta'],
            'resuelta' => ['archivada'],
            'archivada' => [],
        ];

        return $transitions[$currentStatus] ?? [];
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.index')
            ->layout('components.layouts.app', [
                'title' => __('Convocatorias'),
            ]);
    }
}
