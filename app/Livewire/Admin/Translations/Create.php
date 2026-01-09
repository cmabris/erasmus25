<?php

namespace App\Livewire\Admin\Translations;

use App\Http\Requests\StoreTranslationRequest;
use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Translation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;

class Create extends Component
{
    use AuthorizesRequests;

    /**
     * Translatable model type.
     */
    public string $translatableType = '';

    /**
     * Translatable model ID.
     */
    public ?int $translatableId = null;

    /**
     * Language ID.
     */
    public ?int $languageId = null;

    /**
     * Field to translate.
     */
    public string $field = '';

    /**
     * Translation value.
     */
    public string $value = '';

    /**
     * Mount the component.
     *
     * @param  string|null  $model  Model type from URL (optional)
     * @param  int|null  $id  Translatable ID from URL (optional)
     * @param  int|null  $language  Language ID from URL (optional)
     */
    public function mount(?string $model = null, ?int $id = null, ?int $language = null): void
    {
        $this->authorize('create', Translation::class);

        if ($model) {
            $this->translatableType = $model;
        }

        if ($id) {
            $this->translatableId = $id;
        }

        if ($language) {
            $this->languageId = $language;
        }
    }

    /**
     * Reset translatable ID when model type changes.
     */
    public function updatedTranslatableType(): void
    {
        $this->translatableId = null;
        $this->field = '';
    }

    /**
     * Reset field when translatable ID changes.
     */
    public function updatedTranslatableId(): void
    {
        $this->field = '';
    }

    /**
     * Reset validation when any field changes that affects uniqueness.
     */
    public function updatedLanguageId(): void
    {
        $this->resetValidation('value');
    }

    /**
     * Reset validation when field changes.
     */
    public function updatedField(): void
    {
        $this->resetValidation('value');
    }

    /**
     * Get available translatable models.
     *
     * @return array<string, string>
     */
    public function getAvailableModels(): array
    {
        return [
            Program::class => __('Programa'),
            Setting::class => __('Configuración'),
        ];
    }

    /**
     * Get active languages.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLanguages()
    {
        return Language::where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get translatable options based on selected model type.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getTranslatableOptions()
    {
        if (! $this->translatableType) {
            return collect();
        }

        return match ($this->translatableType) {
            Program::class => Program::query()
                ->where('is_active', true)
                ->orderBy('order', 'asc')
                ->orderBy('name', 'asc')
                ->get(),
            Setting::class => Setting::query()
                ->orderBy('key', 'asc')
                ->get(),
            default => collect(),
        };
    }

    /**
     * Get available fields for the selected model.
     *
     * @return array<string, string>
     */
    public function getAvailableFields(): array
    {
        return match ($this->translatableType) {
            Program::class => [
                'name' => __('Nombre'),
                'description' => __('Descripción'),
            ],
            Setting::class => [
                'value' => __('Valor'),
            ],
            default => [],
        };
    }

    /**
     * Check if a translation already exists for the current combination.
     */
    public function translationExists(): bool
    {
        if (! $this->translatableType || ! $this->translatableId || ! $this->languageId || ! $this->field) {
            return false;
        }

        return Translation::where('translatable_type', $this->translatableType)
            ->where('translatable_id', $this->translatableId)
            ->where('language_id', $this->languageId)
            ->where('field', $this->field)
            ->exists();
    }

    /**
     * Get existing translation if it exists.
     */
    public function getExistingTranslation(): ?Translation
    {
        if (! $this->translatableType || ! $this->translatableId || ! $this->languageId || ! $this->field) {
            return null;
        }

        return Translation::where('translatable_type', $this->translatableType)
            ->where('translatable_id', $this->translatableId)
            ->where('language_id', $this->languageId)
            ->where('field', $this->field)
            ->first();
    }

    /**
     * Store the translation.
     */
    public function store(): void
    {
        // Check if translation already exists before validation
        if ($this->translationExists()) {
            $this->addError('value', __('Ya existe una traducción para esta combinación de modelo, idioma y campo.'));
            $this->dispatch('translation-exists');

            return;
        }

        // Prepare data array for validation (map camelCase to snake_case)
        $data = [
            'translatable_type' => $this->translatableType,
            'translatable_id' => $this->translatableId,
            'language_id' => $this->languageId,
            'field' => $this->field,
            'value' => $this->value,
        ];

        // Validate using FormRequest rules and messages
        $rules = (new StoreTranslationRequest)->rules();
        $messages = (new StoreTranslationRequest)->messages();

        try {
            $validated = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages)->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Map validation errors from snake_case to camelCase component properties
            $errors = $e->errors();
            $mappedErrors = [];
            foreach ($errors as $key => $messages) {
                // Map snake_case keys to camelCase component properties
                $componentKey = match ($key) {
                    'translatable_type' => 'translatableType',
                    'translatable_id' => 'translatableId',
                    'language_id' => 'languageId',
                    default => $key,
                };
                $mappedErrors[$componentKey] = $messages;
            }
            throw \Illuminate\Validation\ValidationException::withMessages($mappedErrors);
        }

        Translation::create($validated);

        $this->dispatch('translation-created', [
            'message' => __('common.messages.created_successfully'),
        ]);

        $this->redirect(route('admin.translations.index'), navigate: true);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.translations.create')
            ->layout('components.layouts.app', [
                'title' => __('Crear Traducción'),
            ]);
    }
}
