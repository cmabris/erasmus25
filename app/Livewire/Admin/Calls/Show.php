<?php

namespace App\Livewire\Admin\Calls;

use App\Models\Call;
use App\Models\CallPhase;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The call being displayed.
     */
    public Call $call;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * New status for change status action.
     */
    public string $newStatus = '';

    /**
     * Mount the component.
     */
    public function mount(Call $call): void
    {
        $this->authorize('view', $call);

        // Load relationships with eager loading to avoid N+1 queries
        $this->call = $call->load([
            'program',
            'academicYear',
            'creator',
            'updater',
            'phases' => fn ($query) => $query->orderBy('order'),
            'resolutions' => fn ($query) => $query->latest(),
            'applications' => fn ($query) => $query->latest()->limit(10),
        ])->loadCount(['phases', 'resolutions', 'applications']);
    }

    /**
     * Get statistics for the call.
     * Uses loaded counts to avoid N+1 queries.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_phases' => $this->call->phases_count ?? $this->call->phases()->count(),
            'total_resolutions' => $this->call->resolutions_count ?? $this->call->resolutions()->count(),
            'total_applications' => $this->call->applications_count ?? $this->call->applications()->count(),
        ];
    }

    /**
     * Change call status.
     */
    public function changeStatus(string $status): void
    {
        $this->authorize('update', $this->call);

        $oldStatus = $this->call->status;
        $this->call->status = $status;

        // Update published_at if publishing
        if ($status === 'abierta' && ! $this->call->published_at) {
            $this->call->published_at = now();
        }

        // Update closed_at if closing
        if ($status === 'cerrada' && ! $this->call->closed_at) {
            $this->call->closed_at = now();
        }

        $this->call->updated_by = auth()->id();
        $this->call->save();

        // Reload the call to refresh the view
        $this->call->refresh();

        // Get status labels for message
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
    public function publish(): void
    {
        $this->authorize('publish', $this->call);

        $oldStatus = $this->call->status;

        $this->call->status = 'abierta';
        $this->call->published_at = now();
        $this->call->updated_by = auth()->id();
        $this->call->save();

        // Log activity
        activity()
            ->performedOn($this->call)
            ->causedBy(auth()->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_status' => $oldStatus,
                'new_status' => 'abierta',
                'published_at' => $this->call->published_at?->toIso8601String(),
            ])
            ->log('published');

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('call-published', [
            'message' => __('Convocatoria publicada correctamente. La convocatoria está ahora abierta y visible públicamente.'),
            'title' => __('Convocatoria publicada'),
        ]);
    }

    /**
     * Mark phase as current.
     */
    public function markPhaseAsCurrent(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        // Unset other current phases for this call
        CallPhase::where('call_id', $this->call->id)
            ->where('id', '!=', $phaseId)
            ->update(['is_current' => false]);

        // Mark this phase as current
        $phase->is_current = true;
        $phase->save();

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('phase-updated', [
            'message' => __('Fase marcada como actual correctamente'),
        ]);
    }

    /**
     * Unmark phase as current.
     */
    public function unmarkPhaseAsCurrent(int $phaseId): void
    {
        $phase = CallPhase::findOrFail($phaseId);

        $phase->is_current = false;
        $phase->save();

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('phase-updated', [
            'message' => __('Fase desmarcada como actual correctamente'),
        ]);
    }

    /**
     * Publish resolution.
     */
    public function publishResolution(int $resolutionId): void
    {
        $resolution = Resolution::findOrFail($resolutionId);

        $this->authorize('publish', $resolution);

        $resolution->published_at = now();
        $resolution->save();

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('resolution-published', [
            'message' => __('Resolución publicada correctamente'),
        ]);
    }

    /**
     * Unpublish resolution.
     */
    public function unpublishResolution(int $resolutionId): void
    {
        $resolution = Resolution::findOrFail($resolutionId);

        $this->authorize('publish', $resolution);

        $resolution->published_at = null;
        $resolution->save();

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('resolution-unpublished', [
            'message' => __('Resolución despublicada correctamente'),
        ]);
    }

    /**
     * Delete the call (soft delete).
     */
    public function delete(): void
    {
        // Optimize: Use loaded counts instead of exists() queries
        $hasRelations = ($this->call->phases_count > 0)
            || ($this->call->resolutions_count > 0)
            || ($this->call->applications_count > 0);

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('call-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
                'details' => __('No se puede eliminar esta convocatoria porque tiene :phases fases, :resolutions resoluciones y :applications aplicaciones asociadas.', [
                    'phases' => $this->call->phases_count,
                    'resolutions' => $this->call->resolutions_count,
                    'applications' => $this->call->applications_count,
                ]),
            ]);

            return;
        }

        $this->authorize('delete', $this->call);

        $this->call->delete();

        $this->dispatch('call-deleted', [
            'message' => __('common.messages.deleted_successfully'),
            'title' => __('Convocatoria eliminada'),
        ]);

        $this->redirect(route('admin.calls.index'), navigate: true);
    }

    /**
     * Restore the call.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->call);

        $this->call->restore();
        $this->call->updated_by = auth()->id();
        $this->call->save();

        // Log activity
        activity()
            ->performedOn($this->call)
            ->causedBy(auth()->user())
            ->withProperties([
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ])
            ->log('restored');

        // Reload the call to refresh the view
        $this->call->refresh();

        $this->dispatch('call-restored', [
            'message' => __('common.messages.restored_successfully'),
            'title' => __('Convocatoria restaurada'),
        ]);
    }

    /**
     * Permanently delete the call.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->call);

        // Check relations one more time using loaded counts (more efficient)
        $hasRelations = ($this->call->phases_count > 0)
            || ($this->call->resolutions_count > 0)
            || ($this->call->applications_count > 0);

        if ($hasRelations) {
            $this->dispatch('call-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
                'details' => __('No se puede eliminar permanentemente esta convocatoria porque tiene :phases fases, :resolutions resoluciones y :applications aplicaciones asociadas.', [
                    'phases' => $this->call->phases_count,
                    'resolutions' => $this->call->resolutions_count,
                    'applications' => $this->call->applications_count,
                ]),
            ]);

            return;
        }

        $this->call->forceDelete();

        $this->dispatch('call-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
            'title' => __('Convocatoria eliminada permanentemente'),
        ]);

        $this->redirect(route('admin.calls.index'), navigate: true);
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
     * Get valid status transitions for the current call status.
     */
    public function getValidStatusTransitions(): array
    {
        $transitions = [
            'borrador' => ['abierta', 'en_baremacion'],
            'abierta' => ['cerrada', 'en_baremacion'],
            'cerrada' => ['en_baremacion', 'archivada'],
            'en_baremacion' => ['resuelta', 'abierta'],
            'resuelta' => ['archivada'],
            'archivada' => [],
        ];

        return $transitions[$this->call->status] ?? [];
    }

    /**
     * Get status description.
     */
    public function getStatusDescription(string $status): string
    {
        return match ($status) {
            'borrador' => __('Convocatoria en preparación, no visible públicamente'),
            'abierta' => __('Convocatoria abierta y visible públicamente, aceptando solicitudes'),
            'cerrada' => __('Convocatoria cerrada, ya no acepta nuevas solicitudes'),
            'en_baremacion' => __('Convocatoria en proceso de evaluación y baremación'),
            'resuelta' => __('Convocatoria resuelta, resultados publicados'),
            'archivada' => __('Convocatoria archivada, solo visible para administradores'),
            default => '',
        };
    }

    /**
     * Check if the call can be deleted (has no relationships).
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->call)) {
            return false;
        }

        // Check if it has relationships
        return ! ($this->call->phases()->exists()
            || $this->call->resolutions()->exists()
            || $this->call->applications()->exists());
    }

    /**
     * Check if the call has relationships.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return $this->call->phases()->exists()
            || $this->call->resolutions()->exists()
            || $this->call->applications()->exists();
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.show')
            ->layout('components.layouts.app', [
                'title' => $this->call->title ?? 'Convocatoria',
            ]);
    }
}
