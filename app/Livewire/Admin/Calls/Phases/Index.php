<?php

namespace App\Livewire\Admin\Calls\Phases;

use App\Models\Call;
use App\Models\CallPhase;
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
     * The call that owns these phases.
     */
    public Call $call;

    /**
     * Search query for filtering phases.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by phase type.
     */
    #[Url(as: 'tipo')]
    public string $filterPhaseType = '';

    /**
     * Filter by is_current status.
     */
    #[Url(as: 'actual')]
    public string $filterIsCurrent = '';

    /**
     * Filter to show deleted phases.
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
     * Phase ID to delete (for confirmation).
     */
    public ?int $phaseToDelete = null;

    /**
     * Show restore confirmation modal.
     */
    public bool $showRestoreModal = false;

    /**
     * Phase ID to restore (for confirmation).
     */
    public ?int $phaseToRestore = null;

    /**
     * Show force delete confirmation modal.
     */
    public bool $showForceDeleteModal = false;

    /**
     * Phase ID to force delete (for confirmation).
     */
    public ?int $phaseToForceDelete = null;

    /**
     * Mount the component.
     */
    public function mount(Call $call): void
    {
        $this->authorize('viewAny', CallPhase::class);

        // Load call with minimal relationships for display
        $this->call = $call->load(['program', 'academicYear']);
    }

    /**
     * Get paginated and filtered phases.
     */
    #[Computed]
    public function phases(): LengthAwarePaginator
    {
        return CallPhase::query()
            ->where('call_id', $this->call->id)
            ->when($this->showDeleted === '0', fn ($query) => $query->whereNull('deleted_at'))
            ->when($this->showDeleted === '1', fn ($query) => $query->onlyTrashed())
            ->when($this->filterPhaseType, fn ($query) => $query->where('phase_type', $this->filterPhaseType))
            ->when($this->filterIsCurrent === '1', fn ($query) => $query->where('is_current', true))
            ->when($this->filterIsCurrent === '0', fn ($query) => $query->where('is_current', false))
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->with([
                'call' => fn ($query) => $query->select('id', 'title', 'program_id', 'academic_year_id'),
                'resolutions' => fn ($query) => $query->select('id', 'call_phase_id', 'title', 'type', 'published_at', 'official_date'),
            ])
            ->withCount(['resolutions'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('created_at', 'desc')
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
     * Mark phase as current (only one can be current per call).
     */
    public function markAsCurrent(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        $this->authorize('update', $phase);

        // Unset other current phases for this call
        CallPhase::where('call_id', $this->call->id)
            ->where('id', '!=', $phaseId)
            ->update(['is_current' => false]);

        // Mark this phase as current
        $phase->is_current = true;
        $phase->save();

        $this->dispatch('phase-updated', [
            'message' => __('La fase ":name" ha sido marcada como actual. Las demás fases han sido desmarcadas automáticamente.', ['name' => $phase->name]),
            'title' => __('Fase actualizada'),
        ]);
    }

    /**
     * Unmark phase as current.
     */
    public function unmarkAsCurrent(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        $this->authorize('update', $phase);

        $phase->is_current = false;
        $phase->save();

        $this->dispatch('phase-updated', [
            'message' => __('La fase ":name" ha sido desmarcada como actual.', ['name' => $phase->name]),
            'title' => __('Fase actualizada'),
        ]);
    }

    /**
     * Move phase up in order.
     */
    public function moveUp(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        $this->authorize('update', $phase);

        $previousPhase = CallPhase::where('call_id', $this->call->id)
            ->whereNull('deleted_at')
            ->where('order', '<', $phase->order)
            ->orderBy('order', 'desc')
            ->first();

        if ($previousPhase) {
            $tempOrder = $phase->order;
            $phase->order = $previousPhase->order;
            $previousPhase->order = $tempOrder;
            $phase->save();
            $previousPhase->save();

            $this->dispatch('phase-reordered', [
                'message' => __('La fase ":name" ha sido movida hacia arriba en el orden.', ['name' => $phase->name]),
                'title' => __('Orden actualizado'),
            ]);
        } else {
            $this->dispatch('phase-reorder-error', [
                'message' => __('No se puede mover la fase hacia arriba. Ya está en la primera posición.'),
                'title' => __('Error al reordenar'),
            ]);
        }
    }

    /**
     * Move phase down in order.
     */
    public function moveDown(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        $this->authorize('update', $phase);

        $nextPhase = CallPhase::where('call_id', $this->call->id)
            ->whereNull('deleted_at')
            ->where('order', '>', $phase->order)
            ->orderBy('order', 'asc')
            ->first();

        if ($nextPhase) {
            $tempOrder = $phase->order;
            $phase->order = $nextPhase->order;
            $nextPhase->order = $tempOrder;
            $phase->save();
            $nextPhase->save();

            $this->dispatch('phase-reordered', [
                'message' => __('La fase ":name" ha sido movida hacia abajo en el orden.', ['name' => $phase->name]),
                'title' => __('Orden actualizado'),
            ]);
        } else {
            $this->dispatch('phase-reorder-error', [
                'message' => __('No se puede mover la fase hacia abajo. Ya está en la última posición.'),
                'title' => __('Error al reordenar'),
            ]);
        }
    }

    /**
     * Confirm delete action.
     */
    public function confirmDelete(int $phaseId): void
    {
        $this->phaseToDelete = $phaseId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a phase (SoftDelete).
     */
    public function delete(): void
    {
        if (! $this->phaseToDelete) {
            return;
        }

        // Load phase with counts
        $phase = CallPhase::withCount(['resolutions'])
            ->findOrFail($this->phaseToDelete);

        // Check if phase has resolutions
        if ($phase->resolutions_count > 0) {
            $this->showDeleteModal = false;
            $this->phaseToDelete = null;
            $this->dispatch('phase-delete-error', [
                'message' => __('No se puede eliminar esta fase porque tiene :count resolución(es) asociada(s).', ['count' => $phase->resolutions_count]),
                'title' => __('Error al eliminar'),
            ]);

            return;
        }

        $this->authorize('delete', $phase);

        $phaseName = $phase->name;
        $phase->delete();

        $this->phaseToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('phase-deleted', [
            'message' => __('La fase ":name" ha sido eliminada correctamente. Puede restaurarla desde la sección de eliminados.', ['name' => $phaseName]),
            'title' => __('Fase eliminada'),
        ]);
    }

    /**
     * Confirm restore action.
     */
    public function confirmRestore(int $phaseId): void
    {
        $this->phaseToRestore = $phaseId;
        $this->showRestoreModal = true;
    }

    /**
     * Restore a deleted phase.
     */
    public function restore(): void
    {
        if (! $this->phaseToRestore) {
            return;
        }

        $phase = CallPhase::onlyTrashed()->findOrFail($this->phaseToRestore);

        $this->authorize('restore', $phase);

        $phaseName = $phase->name;
        $phase->restore();

        $this->phaseToRestore = null;
        $this->showRestoreModal = false;
        $this->resetPage();

        $this->dispatch('phase-restored', [
            'message' => __('La fase ":name" ha sido restaurada correctamente.', ['name' => $phaseName]),
            'title' => __('Fase restaurada'),
        ]);
    }

    /**
     * Confirm force delete action.
     */
    public function confirmForceDelete(int $phaseId): void
    {
        $this->phaseToForceDelete = $phaseId;
        $this->showForceDeleteModal = true;
    }

    /**
     * Force delete a phase (permanent deletion).
     */
    public function forceDelete(): void
    {
        if (! $this->phaseToForceDelete) {
            return;
        }

        // Load phase with counts
        $phase = CallPhase::onlyTrashed()
            ->withCount(['resolutions'])
            ->findOrFail($this->phaseToForceDelete);

        $this->authorize('forceDelete', $phase);

        // Verify no resolutions exist
        if ($phase->resolutions_count > 0) {
            $this->showForceDeleteModal = false;
            $this->dispatch('phase-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente esta fase porque tiene :count resolución(es) asociada(s). Primero debe eliminar o reasignar las resoluciones.', ['count' => $phase->resolutions_count]),
                'title' => __('Error al eliminar'),
            ]);

            return;
        }

        $phaseName = $phase->name;
        $phase->forceDelete();

        $this->phaseToForceDelete = null;
        $this->showForceDeleteModal = false;
        $this->resetPage();

        $phaseName = $phase->name;
        $phase->forceDelete();

        $this->dispatch('phase-force-deleted', [
            'message' => __('La fase ":name" ha sido eliminada permanentemente del sistema. Esta acción no se puede revertir.', ['name' => $phaseName]),
            'title' => __('Fase eliminada permanentemente'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterPhaseType', 'filterIsCurrent', 'showDeleted']);
        $this->showDeleted = '0';
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
    public function updatedFilterPhaseType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterIsCurrent(): void
    {
        $this->resetPage();
    }

    public function updatedShowDeleted(): void
    {
        $this->resetPage();
    }

    /**
     * Get phase type options.
     */
    public function getPhaseTypeOptions(): array
    {
        return [
            'publicacion' => __('Publicación'),
            'solicitudes' => __('Solicitudes'),
            'provisional' => __('Provisional'),
            'alegaciones' => __('Alegaciones'),
            'definitivo' => __('Definitivo'),
            'renuncias' => __('Renuncias'),
            'lista_espera' => __('Lista de Espera'),
        ];
    }

    /**
     * Get phase type badge color.
     */
    public function getPhaseTypeColor(string $phaseType): string
    {
        return match ($phaseType) {
            'publicacion' => 'blue',
            'solicitudes' => 'green',
            'provisional' => 'yellow',
            'alegaciones' => 'orange',
            'definitivo' => 'purple',
            'renuncias' => 'red',
            'lista_espera' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get status badge color for call.
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
     * Check if user can create phases.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', CallPhase::class) ?? false;
    }

    /**
     * Check if user can view deleted phases.
     */
    public function canViewDeleted(): bool
    {
        return auth()->user()?->can('viewAny', CallPhase::class) ?? false;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.phases.index')
            ->layout('components.layouts.app', [
                'title' => __('Fases de Convocatoria'),
            ]);
    }
}
