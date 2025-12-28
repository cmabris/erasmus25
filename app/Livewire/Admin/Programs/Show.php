<?php

namespace App\Livewire\Admin\Programs;

use App\Models\Language;
use App\Models\Program;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The program being displayed.
     */
    public Program $program;

    /**
     * Modal states.
     */
    public bool $showDeleteModal = false;

    public bool $showRestoreModal = false;

    public bool $showForceDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Program $program): void
    {
        $this->authorize('view', $program);

        // Load relationships with eager loading for better performance
        $this->program = $program->load([
            'calls' => fn ($query) => $query->latest()->limit(5),
            'newsPosts' => fn ($query) => $query->latest()->limit(5),
        ]);
    }

    /**
     * Get the program image URL.
     */
    #[Computed]
    public function imageUrl(): ?string
    {
        return $this->program->getFirstMediaUrl('image');
    }

    /**
     * Get the program image URL with conversion.
     */
    public function getImageUrl(string $conversion = 'large'): ?string
    {
        return $this->program->getFirstMediaUrl('image', $conversion);
    }

    /**
     * Check if program has image.
     */
    #[Computed]
    public function hasImage(): bool
    {
        return $this->program->hasMedia('image');
    }

    /**
     * Get statistics for the program.
     */
    #[Computed]
    public function statistics(): array
    {
        return [
            'total_calls' => $this->program->calls()->count(),
            'active_calls' => $this->program->calls()->where('status', 'abierta')->count(),
            'total_news' => $this->program->newsPosts()->count(),
            'published_news' => $this->program->newsPosts()->where('status', 'publicado')->count(),
        ];
    }

    /**
     * Toggle active status.
     */
    public function toggleActive(): void
    {
        $this->authorize('update', $this->program);

        $this->program->update([
            'is_active' => ! $this->program->is_active,
        ]);

        $this->dispatch('program-updated', [
            'message' => $this->program->is_active
                ? __('Programa activado correctamente')
                : __('Programa desactivado correctamente'),
        ]);
    }

    /**
     * Delete the program (soft delete).
     */
    public function delete(): void
    {
        // Check if program has relationships
        $hasRelations = $this->program->calls()->exists() || $this->program->newsPosts()->exists();
        if ($hasRelations) {
            $this->showDeleteModal = false;
            $this->dispatch('program-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->authorize('delete', $this->program);

        $this->program->delete();

        $this->dispatch('program-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.programs.index'), navigate: true);
    }

    /**
     * Restore the program.
     */
    public function restore(): void
    {
        $this->authorize('restore', $this->program);

        $this->program->restore();

        $this->dispatch('program-restored', [
            'message' => __('common.messages.restored_successfully'),
        ]);

        $this->redirect(route('admin.programs.show', $this->program), navigate: true);
    }

    /**
     * Permanently delete the program.
     */
    public function forceDelete(): void
    {
        $this->authorize('forceDelete', $this->program);

        // Check relations one more time
        $hasRelations = $this->program->calls()->exists() || $this->program->newsPosts()->exists();

        if ($hasRelations) {
            $this->dispatch('program-force-delete-error', [
                'message' => __('common.errors.cannot_delete_with_relations'),
            ]);

            return;
        }

        $this->program->forceDelete();

        $this->dispatch('program-force-deleted', [
            'message' => __('common.messages.permanently_deleted_successfully'),
        ]);

        $this->redirect(route('admin.programs.index'), navigate: true);
    }

    /**
     * Check if the program can be deleted (has no relationships).
     */
    public function canDelete(): bool
    {
        return auth()->user()?->can('delete', $this->program) ?? false;
    }

    /**
     * Check if the program has relationships.
     */
    #[Computed]
    public function hasRelationships(): bool
    {
        return $this->program->calls()->exists() || $this->program->newsPosts()->exists();
    }

    /**
     * Get available translations for the program.
     */
    #[Computed]
    public function availableTranslations(): array
    {
        $languages = Language::where('is_active', true)->get();
        $translations = [];

        foreach ($languages as $language) {
            $nameTranslation = $this->program->translate('name', $language->code);
            $descriptionTranslation = $this->program->translate('description', $language->code);

            if ($nameTranslation || $descriptionTranslation) {
                $translations[] = [
                    'language' => $language,
                    'name' => $nameTranslation,
                    'description' => $descriptionTranslation,
                ];
            }
        }

        return $translations;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.programs.show')
            ->layout('components.layouts.app', [
                'title' => $this->program->name,
            ]);
    }
}
