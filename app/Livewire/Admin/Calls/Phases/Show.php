<?php

namespace App\Livewire\Admin\Calls\Phases;

use App\Models\Call;
use App\Models\CallPhase;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The call that owns this phase.
     */
    public Call $call;

    /**
     * The phase being displayed.
     */
    public CallPhase $callPhase;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Call $call, CallPhase $call_phase): void
    {
        $this->authorize('view', $call_phase);

        $this->call = $call;

        // Load relationships with eager loading to avoid N+1 queries
        // Optimize by selecting only needed columns
        $this->callPhase = $call_phase->load([
            'call' => fn ($query) => $query->with([
                'program' => fn ($q) => $q->select('id', 'name'),
                'academicYear' => fn ($q) => $q->select('id', 'year'),
            ])->select('id', 'title', 'program_id', 'academic_year_id', 'status'),
            'resolutions' => fn ($query) => $query->select('id', 'call_phase_id', 'call_id', 'title', 'type', 'description', 'official_date', 'published_at', 'created_at')
                ->latest(),
        ])->loadCount(['resolutions']);
    }

    /**
     * Get statistics for the phase.
     * Uses loaded counts to avoid N+1 queries.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_resolutions' => $this->callPhase->resolutions_count ?? $this->callPhase->resolutions()->count(),
        ];
    }

    /**
     * Mark phase as current.
     */
    public function markAsCurrent(): void
    {
        $this->authorize('update', $this->callPhase);

        // Unset other current phases for this call
        CallPhase::where('call_id', $this->call->id)
            ->where('id', '!=', $this->callPhase->id)
            ->update(['is_current' => false]);

        // Mark this phase as current
        $this->callPhase->is_current = true;
        $this->callPhase->save();

        // Reload the phase to refresh the view
        $this->callPhase->refresh();

        $this->dispatch('phase-updated', [
            'message' => __('Fase marcada como actual correctamente'),
            'title' => __('Fase actualizada'),
        ]);
    }

    /**
     * Unmark phase as current.
     */
    public function unmarkAsCurrent(): void
    {
        $this->authorize('update', $this->callPhase);

        $this->callPhase->is_current = false;
        $this->callPhase->save();

        // Reload the phase to refresh the view
        $this->callPhase->refresh();

        $this->dispatch('phase-updated', [
            'message' => __('Fase desmarcada como actual correctamente'),
            'title' => __('Fase actualizada'),
        ]);
    }

    /**
     * Delete the phase (soft delete).
     */
    public function delete(): void
    {
        // Check if phase has resolutions
        $hasRelations = $this->callPhase->resolutions()->exists();

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('phase-delete-error', [
                'message' => __('No se puede eliminar esta fase porque tiene :count resolución(es) asociada(s).', [
                    'count' => $this->callPhase->resolutions()->count(),
                ]),
                'title' => __('Error al eliminar'),
            ]);

            return;
        }

        $this->authorize('delete', $this->callPhase);

        $this->callPhase->delete();

        $this->dispatch('phase-deleted', [
            'message' => __('common.messages.deleted_successfully'),
            'title' => __('Fase eliminada'),
        ]);

        $this->redirect(route('admin.calls.phases.index', $this->call), navigate: true);
    }

    /**
     * Restore the phase.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->callPhase);

        $this->callPhase->restore();

        // Reload the phase to refresh the view
        $this->callPhase->refresh();

        $this->dispatch('phase-restored', [
            'message' => __('common.messages.restored_successfully'),
            'title' => __('Fase restaurada'),
        ]);
    }

    /**
     * Permanently delete the phase.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->callPhase);

        // Check relations one more time
        $hasRelations = $this->callPhase->resolutions()->exists();

        if ($hasRelations) {
            $this->showForceDeleteModal = false;
            $this->dispatch('phase-force-delete-error', [
                'message' => __('No se puede eliminar permanentemente esta fase porque tiene :count resolución(es) asociada(s).', [
                    'count' => $this->callPhase->resolutions()->count(),
                ]),
                'title' => __('Error al eliminar'),
            ]);

            return;
        }

        $this->callPhase->forceDelete();

        $this->dispatch('phase-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
            'title' => __('Fase eliminada permanentemente'),
        ]);

        $this->redirect(route('admin.calls.phases.index', $this->call), navigate: true);
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
     * Get phase type label.
     */
    public function getPhaseTypeLabel(string $phaseType): string
    {
        return match ($phaseType) {
            'publicacion' => __('Publicación'),
            'solicitudes' => __('Solicitudes'),
            'provisional' => __('Provisional'),
            'alegaciones' => __('Alegaciones'),
            'definitivo' => __('Definitivo'),
            'renuncias' => __('Renuncias'),
            'lista_espera' => __('Lista de Espera'),
            default => $phaseType,
        };
    }

    /**
     * Check if the phase can be deleted (has no relationships).
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->callPhase)) {
            return false;
        }

        // Check if it has relationships
        return ! $this->callPhase->resolutions()->exists();
    }

    /**
     * Check if the phase has relationships.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return $this->callPhase->resolutions()->exists();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.phases.show')
            ->layout('components.layouts.app', [
                'title' => $this->callPhase->name ?? 'Fase',
            ]);
    }
}
