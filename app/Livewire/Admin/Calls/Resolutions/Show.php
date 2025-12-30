<?php

namespace App\Livewire\Admin\Calls\Resolutions;

use App\Models\Call;
use App\Models\Resolution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The call that owns this resolution.
     */
    public Call $call;

    /**
     * The resolution being displayed.
     */
    public Resolution $resolution;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Call $call, Resolution $resolution): void
    {
        $this->authorize('view', $resolution);

        $this->call = $call;

        // Load relationships with eager loading to avoid N+1 queries
        $this->resolution = $resolution->load([
            'call' => fn ($query) => $query->with([
                'program' => fn ($q) => $q->select('id', 'name'),
                'academicYear' => fn ($q) => $q->select('id', 'year'),
            ])->select('id', 'title', 'program_id', 'academic_year_id', 'status'),
            'callPhase' => fn ($query) => $query->select('id', 'call_id', 'name', 'phase_type', 'order'),
            'creator' => fn ($query) => $query->select('id', 'name', 'email'),
        ]);
    }

    /**
     * Publish the resolution.
     */
    public function publish(): void
    {
        $this->authorize('publish', $this->resolution);

        $this->resolution->published_at = now();
        $this->resolution->save();

        // Reload the resolution to refresh the view
        $this->resolution->refresh();

        $this->dispatch('resolution-published', [
            'message' => __('La resolución ":title" ha sido publicada correctamente.', ['title' => $this->resolution->title]),
            'title' => __('Resolución publicada'),
        ]);
    }

    /**
     * Unpublish the resolution.
     */
    public function unpublish(): void
    {
        $this->authorize('publish', $this->resolution);

        $this->resolution->published_at = null;
        $this->resolution->save();

        // Reload the resolution to refresh the view
        $this->resolution->refresh();

        $this->dispatch('resolution-unpublished', [
            'message' => __('La resolución ":title" ha sido despublicada correctamente.', ['title' => $this->resolution->title]),
            'title' => __('Resolución despublicada'),
        ]);
    }

    /**
     * Delete the resolution (soft delete).
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->resolution);

        $resolutionTitle = $this->resolution->title;
        $this->resolution->delete();

        $this->dispatch('resolution-deleted', [
            'message' => __('La resolución ":title" ha sido eliminada correctamente. Puede restaurarla desde la sección de eliminados.', ['title' => $resolutionTitle]),
            'title' => __('Resolución eliminada'),
        ]);

        $this->redirect(route('admin.calls.resolutions.index', $this->call), navigate: true);
    }

    /**
     * Restore the resolution.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->resolution);

        $this->resolution->restore();

        // Reload the resolution to refresh the view
        $this->resolution->refresh();

        $this->dispatch('resolution-restored', [
            'message' => __('La resolución ":title" ha sido restaurada correctamente.', ['title' => $this->resolution->title]),
            'title' => __('Resolución restaurada'),
        ]);
    }

    /**
     * Permanently delete the resolution.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->resolution);

        $resolutionTitle = $this->resolution->title;
        $this->resolution->forceDelete();

        $this->dispatch('resolution-force-deleted', [
            'message' => __('La resolución ":title" ha sido eliminada permanentemente del sistema. Esta acción no se puede revertir.', ['title' => $resolutionTitle]),
            'title' => __('Resolución eliminada permanentemente'),
        ]);

        $this->redirect(route('admin.calls.resolutions.index', $this->call), navigate: true);
    }

    /**
     * Get resolution type badge color.
     */
    public function getTypeColor(string $type): string
    {
        return match ($type) {
            'provisional' => 'yellow',
            'definitivo' => 'green',
            'alegaciones' => 'orange',
            default => 'gray',
        };
    }

    /**
     * Get resolution type label.
     */
    public function getTypeLabel(string $type): string
    {
        return match ($type) {
            'provisional' => __('Provisional'),
            'definitivo' => __('Definitivo'),
            'alegaciones' => __('Alegaciones'),
            default => $type,
        };
    }

    /**
     * Get existing PDF media.
     */
    #[Computed]
    public function existingPdf()
    {
        return $this->resolution->getFirstMedia('resolutions');
    }

    /**
     * Check if resolution has PDF.
     */
    public function hasPdf(): bool
    {
        return $this->resolution->hasMedia('resolutions');
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.calls.resolutions.show')
            ->layout('components.layouts.app', [
                'title' => $this->resolution->title ?? 'Resolución',
            ]);
    }
}
