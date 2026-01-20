<?php

namespace App\Livewire\Admin\AcademicYears;

use App\Models\AcademicYear;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The academic year being displayed.
     */
    public AcademicYear $academicYear;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * The academic year ID (for route generation).
     */
    public ?int $academicYearId = null;

    /**
     * Mount the component.
     */
    public function mount(AcademicYear $academic_year): void
    {
        $this->authorize('view', $academic_year);

        // Store the ID separately for route generation
        $this->academicYearId = $academic_year->id;

        // Load relationships with eager loading and counts to avoid N+1 queries
        $this->academicYear = $academic_year->load([
            'calls' => fn ($query) => $query->with('program')->latest()->limit(5),
            'newsPosts' => fn ($query) => $query->with(['author', 'program'])->latest()->limit(5),
            'documents' => fn ($query) => $query->with(['category', 'program'])->latest()->limit(5),
        ])->loadCount(['calls', 'newsPosts', 'documents']);
    }

    /**
     * Get the academic year ID.
     */
    #[Computed]
    public function academicYearId(): int
    {
        return $this->academicYear->id;
    }

    /**
     * Get statistics for the academic year.
     * Uses loaded counts to avoid N+1 queries.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_calls' => $this->academicYear->calls_count ?? $this->academicYear->calls()->count(),
            'total_news' => $this->academicYear->news_posts_count ?? $this->academicYear->newsPosts()->count(),
            'total_documents' => $this->academicYear->documents_count ?? $this->academicYear->documents()->count(),
        ];
    }

    /**
     * Toggle current status (mark/unmark as current academic year).
     */
    public function toggleCurrent(): void
    {
        $this->authorize('update', $this->academicYear);

        if ($this->academicYear->is_current) {
            // Unmark as current
            $this->academicYear->unmarkAsCurrent();
            $message = __('Año académico desmarcado como actual correctamente');
        } else {
            // Mark as current - this will automatically unmark others
            $this->academicYear->markAsCurrent();
            $message = __('Año académico marcado como actual correctamente');
        }

        // Reload the academic year to refresh the view
        $this->academicYear->refresh();

        $this->dispatch('academic-year-updated', [
            'message' => $message,
        ]);
    }

    /**
     * Delete the academic year (soft delete).
     */
    public function delete(): void
    {
        // Check if academic year has relationships
        $hasRelations = $this->academicYear->calls()->exists()
            || $this->academicYear->newsPosts()->exists()
            || $this->academicYear->documents()->exists();

        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('academic-year-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->authorize('delete', $this->academicYear);

        $this->academicYear->delete();

        $this->dispatch('academic-year-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.academic-years.index'), navigate: true);
    }

    /**
     * Restore the academic year.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->academicYear);

        $this->academicYear->restore();

        $this->dispatch('academic-year-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);

        // Reload the academic year to refresh the view
        $this->academicYear->refresh();
    }

    /**
     * Permanently delete the academic year.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->academicYear);

        // Check relations one more time
        $hasRelations = $this->academicYear->calls()->exists()
            || $this->academicYear->newsPosts()->exists()
            || $this->academicYear->documents()->exists();

        if ($hasRelations) {
            $this->dispatch('academic-year-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->academicYear->forceDelete();

        $this->dispatch('academic-year-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.academic-years.index'), navigate: true);
    }

    /**
     * Check if the academic year can be deleted (has no relationships).
     */
    public function canDelete(): bool
    {
        if (! auth()->user()?->can('delete', $this->academicYear)) {
            return false;
        }

        // Check if it has relationships
        return ! ($this->academicYear->calls()->exists()
            || $this->academicYear->newsPosts()->exists()
            || $this->academicYear->documents()->exists());
    }

    /**
     * Check if the academic year has relationships.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return $this->academicYear->calls()->exists()
            || $this->academicYear->newsPosts()->exists()
            || $this->academicYear->documents()->exists();
    }

    /**
     * Get the edit URL for the academic year.
     */
    #[Computed]
    public function editUrl(): string
    {
        if (! isset($this->academicYearId)) {
            return '#';
        }

        try {
            return route('admin.academic-years.edit', $this->academicYearId);
        } catch (\Exception $e) {
            return '#';
        }
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.academic-years.show', [
            'academicYear' => $this->academicYear,
        ])
            ->layout('components.layouts.app', [
                'title' => $this->academicYear->year ?? 'Año Académico',
            ]);
    }
}
