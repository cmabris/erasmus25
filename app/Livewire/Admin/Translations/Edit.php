<?php

namespace App\Livewire\Admin\Translations;

use App\Models\Program;
use App\Models\Translation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Edit extends Component
{
    use AuthorizesRequests;

    /**
     * The translation being edited.
     */
    public Translation $translation;

    /**
     * Translation value.
     */
    public string $value = '';

    /**
     * Mount the component.
     */
    public function mount(Translation $translation): void
    {
        $this->authorize('update', $translation);

        $this->translation = $translation->load(['language', 'translatable']);

        // Load translation value
        $this->value = $translation->value;
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
     * Get translatable display name.
     */
    public function getTranslatableDisplayName(): string
    {
        $translatable = $this->translation->translatable;

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
     * Update the translation.
     */
    public function update(): void
    {
        // Prepare data array for validation
        $data = [
            'value' => $this->value,
        ];

        // Get validation rules manually (since we can't use FormRequest directly in Livewire)
        $translationId = $this->translation->id;
        $translatableType = $this->translation->translatable_type;
        $translatableId = $this->translation->translatable_id;
        $languageId = $this->translation->language_id;
        $field = $this->translation->field;

        $rules = [
            'value' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($translatableType, $translatableId, $languageId, $field, $translationId) {
                    $exists = \App\Models\Translation::where('translatable_type', $translatableType)
                        ->where('translatable_id', $translatableId)
                        ->where('language_id', $languageId)
                        ->where('field', $field)
                        ->where('id', '!=', $translationId)
                        ->exists();

                    if ($exists) {
                        $fail(__('Ya existe una traducción para esta combinación de modelo, idioma y campo.'));
                    }
                },
            ],
        ];

        $messages = [
            'value.required' => __('El valor de la traducción es obligatorio.'),
            'value.string' => __('El valor de la traducción debe ser un texto válido.'),
        ];

        $validated = $this->validate($rules, $messages);

        $this->translation->update($validated);

        $this->dispatch('translation-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.translations.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.translations.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Traducción'),
            ]);
    }
}
