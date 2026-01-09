<?php

namespace App\Livewire\Admin\Translations;

use App\Models\Program;
use App\Models\Translation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The translation being displayed.
     */
    public Translation $translation;

    /**
     * Show delete confirmation modal.
     */
    public bool $showDeleteModal = false;

    /**
     * Mount the component.
     */
    public function mount(Translation $translation): void
    {
        $this->authorize('view', $translation);

        // Load relationships with eager loading to avoid N+1 queries
        $this->translation = $translation->load(['language']);
    }

    /**
     * Get model type display name.
     */
    public function getModelTypeDisplayName(?string $modelType): string
    {
        if (! $modelType) {
            return '-';
        }

        return match ($modelType) {
            Program::class => __('Programa'),
            \App\Models\Setting::class => __('Configuración'),
            default => class_basename($modelType),
        };
    }

    /**
     * Get translatable model (including soft-deleted).
     */
    public function getTranslatableModel()
    {
        if (! $this->translation->translatable_type || ! $this->translation->translatable_id) {
            return null;
        }

        $modelClass = $this->translation->translatable_type;

        if (! class_exists($modelClass)) {
            return null;
        }

        // Check if model uses SoftDeletes
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($modelClass),
            true
        );

        return $usesSoftDeletes
            ? $modelClass::withTrashed()->find($this->translation->translatable_id)
            : $modelClass::find($this->translation->translatable_id);
    }

    /**
     * Get translatable display name.
     */
    public function getTranslatableDisplayName(): string
    {
        $translatable = $this->getTranslatableModel();

        if (! $translatable) {
            return __('Registro eliminado');
        }

        return match ($this->translation->translatable_type) {
            Program::class => $translatable->code.' - '.$translatable->name,
            \App\Models\Setting::class => $translatable->key,
            default => class_basename($this->translation->translatable_type).' #'.$this->translation->translatable_id,
        };
    }

    /**
     * Check if translatable model is soft-deleted.
     */
    public function isTranslatableDeleted(): bool
    {
        $translatable = $this->getTranslatableModel();

        if (! $translatable) {
            return true;
        }

        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(get_class($translatable)),
            true
        );

        return $usesSoftDeletes && $translatable->trashed();
    }

    /**
     * Get route for translatable model.
     */
    public function getTranslatableRoute(): ?string
    {
        $translatable = $this->getTranslatableModel();

        if (! $translatable) {
            return null;
        }

        return match ($this->translation->translatable_type) {
            Program::class => route('admin.programs.show', $translatable),
            \App\Models\Setting::class => route('admin.settings.show', $translatable),
            default => null,
        };
    }

    /**
     * Show delete confirmation modal.
     */
    public function confirmDelete(): void
    {
        $this->authorize('delete', $this->translation);
        $this->showDeleteModal = true;
    }

    /**
     * Delete the translation.
     */
    public function delete(): void
    {
        $this->authorize('delete', $this->translation);

        $this->translation->delete();

        $this->dispatch('translation-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);

        $this->redirect(route('admin.translations.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.translations.show')
            ->layout('components.layouts.app', [
                'title' => __('Ver Traducción'),
            ]);
    }
}
