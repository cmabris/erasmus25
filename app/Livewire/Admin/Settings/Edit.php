<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Language;
use App\Models\Setting;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\LivewireFilepond\WithFilePond;

class Edit extends Component
{
    use AuthorizesRequests;
    use WithFilePond;
    use WithFileUploads;

    /**
     * The setting being edited.
     */
    public Setting $setting;

    /**
     * Setting value (type-dependent).
     */
    public mixed $value = null;

    /**
     * Setting description.
     */
    public string $description = '';

    /**
     * Translations for description and value (if string type).
     */
    public array $translations = [];

    /**
     * JSON preview (formatted).
     */
    public ?string $jsonPreview = null;

    /**
     * Logo file upload (for center_logo setting).
     */
    public ?UploadedFile $logoFile = null;

    /**
     * Whether to remove existing logo.
     */
    public bool $removeExistingLogo = false;

    /**
     * Mount the component.
     */
    public function mount(Setting $setting): void
    {
        $this->authorize('update', $setting);

        $this->setting = $setting;

        // Load setting data
        // For JSON type, we need to get the raw value from database (before accessor conversion)
        // and convert it to a formatted JSON string for editing
        if ($setting->type === 'json') {
            $rawValue = $setting->getAttributes()['value'] ?? null;
            if ($rawValue) {
                $decoded = json_decode($rawValue, true);
                if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                    $this->value = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $this->jsonPreview = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $this->value = $rawValue;
                    $this->jsonPreview = null;
                }
            } else {
                $this->value = '';
                $this->jsonPreview = null;
            }
        } else {
            $this->value = $setting->value;
        }
        $this->description = $setting->description ?? '';

        // Load existing translations
        $this->loadTranslations();
    }

    /**
     * Load existing translations for all languages.
     */
    public function loadTranslations(): void
    {
        $languages = Language::where('is_active', true)->get();
        $this->translations = [];

        foreach ($languages as $language) {
            $this->translations[$language->code] = [
                'description' => $this->setting->translate('description', $language->code) ?? '',
            ];

            // Only allow value translation for string type
            if ($this->setting->type === 'string') {
                $this->translations[$language->code]['value'] = $this->setting->translate('value', $language->code) ?? '';
            }
        }
    }

    /**
     * Get available languages.
     */
    public function getAvailableLanguagesProperty()
    {
        return Language::where('is_active', true)->orderBy('code')->get();
    }

    /**
     * Validate value when it changes (real-time validation).
     */
    public function updatedValue($value): void
    {
        $this->validateValue($value);
    }

    /**
     * Validate value according to type.
     */
    public function validateValue($value): void
    {
        $rules = match ($this->setting->type) {
            'integer' => ['required', 'integer'],
            'boolean' => ['required', 'boolean'],
            'json' => ['required', 'json'],
            default => ['required', 'string'],
        };

        $messages = match ($this->setting->type) {
            'integer' => [
                'value.required' => __('El valor es obligatorio.'),
                'value.integer' => __('El valor debe ser un número entero válido.'),
            ],
            'boolean' => [
                'value.required' => __('El valor es obligatorio.'),
                'value.boolean' => __('El valor debe ser un booleano (sí/no).'),
            ],
            'json' => [
                'value.required' => __('El valor es obligatorio.'),
                'value.json' => __('El valor debe ser un JSON válido. Verifique la sintaxis.'),
            ],
            default => [
                'value.required' => __('El valor es obligatorio.'),
                'value.string' => __('El valor debe ser un texto válido.'),
            ],
        };

        // Use Validator::make to validate only the 'value' field without affecting other fields
        $validator = Validator::make(['value' => $value], $rules, $messages);

        if ($validator->fails()) {
            // Add errors manually to component
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }
        } else {
            // Clear errors if validation passes
            $this->resetErrorBag('value');
        }

        // Update JSON preview if type is json
        if ($this->setting->type === 'json') {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
                    $this->jsonPreview = json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                } else {
                    $this->jsonPreview = null;
                }
            } elseif (is_array($value) || is_object($value)) {
                $this->jsonPreview = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            } else {
                $this->jsonPreview = null;
            }
        }
    }

    /**
     * Show confirmation before updating.
     */
    public bool $showUpdateModal = false;

    /**
     * Check if this is the center_logo setting.
     */
    public function isCenterLogo(): bool
    {
        return $this->setting->key === 'center_logo';
    }

    /**
     * Get the current logo URL if exists.
     */
    public function getCurrentLogoUrl(): ?string
    {
        if (! $this->isCenterLogo()) {
            return null;
        }

        $logoPath = $this->setting->value;

        if (empty($logoPath)) {
            return null;
        }

        // If it's already a full URL, return it
        if (filter_var($logoPath, FILTER_VALIDATE_URL)) {
            return $logoPath;
        }

        // If it's a storage path, return the public URL
        if (str_starts_with($logoPath, 'logos/')) {
            return Storage::disk('public')->url($logoPath);
        }

        // If it's a public path, return as is
        if (str_starts_with($logoPath, '/')) {
            return $logoPath;
        }

        return null;
    }

    /**
     * Remove existing logo.
     */
    public function removeLogo(): void
    {
        $this->removeExistingLogo = true;
        $this->logoFile = null;
    }

    /**
     * Validate uploaded logo file.
     */
    public function validateUploadedFile(string $filename): bool
    {
        // This method is called by FilePond after upload
        // The file is already validated by FilePond client-side
        // We can add additional server-side validation here if needed
        return true;
    }

    /**
     * Get validation rules based on setting type.
     */
    protected function getValidationRules(): array
    {
        $type = $this->setting->type;

        // Special handling for center_logo with file upload
        if ($this->isCenterLogo() && $this->logoFile) {
            return [
                'logoFile' => ['image', 'max:5120', 'mimes:jpeg,jpg,png,webp,svg'], // 5MB max
                'description' => ['nullable', 'string'],
            ];
        }

        // Reglas base para value según tipo
        $valueRules = match ($type) {
            'integer' => ['required', 'integer'],
            'boolean' => ['required', 'boolean'],
            'json' => ['required', 'json'],
            default => ['required', 'string'],
        };

        $rules = [
            'value' => $valueRules,
            'description' => ['nullable', 'string'],
        ];

        // For center_logo, value is not required if we're uploading a file
        if ($this->isCenterLogo() && ($this->logoFile || $this->removeExistingLogo)) {
            $rules['value'] = ['nullable', 'string'];
        }

        return $rules;
    }

    /**
     * Get validation messages.
     */
    protected function getValidationMessages(): array
    {
        $typeLabel = match ($this->setting->type) {
            'integer' => __('número entero'),
            'boolean' => __('booleano (sí/no)'),
            'json' => __('JSON válido'),
            default => __('texto'),
        };

        return [
            'value.required' => __('El valor de la configuración es obligatorio.'),
            'value.integer' => __('El valor debe ser un número entero válido.'),
            'value.boolean' => __('El valor debe ser un booleano (sí/no). Use "1" o "true" para activar, "0" o "false" para desactivar.'),
            'value.json' => __('El valor debe ser un JSON válido. Verifique la sintaxis (llaves, comas, comillas).'),
            'value.string' => __('El valor debe ser un texto válido.'),
            'description.string' => __('La descripción debe ser un texto válido.'),
        ];
    }

    /**
     * Confirm update action.
     */
    public function confirmUpdate(): void
    {
        // Build data array for validation
        $data = [
            'value' => $this->value,
            'description' => $this->description ?? null,
        ];

        // Add logo file to validation data if present
        if ($this->isCenterLogo() && $this->logoFile) {
            $data['logoFile'] = $this->logoFile;
        }

        // Get rules and messages
        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();

        // Validate using Validator::make directly
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            // Add errors manually to component
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }

            return;
        }

        // Show confirmation modal if validation passes
        $this->showUpdateModal = true;
    }

    /**
     * Update the setting.
     */
    public function update(): void
    {
        // Build data array for validation
        $data = [
            'value' => $this->value,
            'description' => $this->description ?? null,
        ];

        // Add logo file to validation data if present
        if ($this->isCenterLogo() && $this->logoFile) {
            $data['logoFile'] = $this->logoFile;
        }

        // Get rules and messages
        $rules = $this->getValidationRules();
        $messages = $this->getValidationMessages();

        // Validate using Validator::make directly
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            // Add errors manually to component
            foreach ($validator->errors()->messages() as $key => $errorMessages) {
                foreach ($errorMessages as $message) {
                    $this->addError($key, $message);
                }
            }

            $this->showUpdateModal = false;

            return;
        }

        $validated = $validator->validated();

        // Handle logo file upload for center_logo
        if ($this->isCenterLogo()) {
            // Remove existing logo if requested
            if ($this->removeExistingLogo) {
                $oldLogoPath = $this->setting->value;
                if ($oldLogoPath && str_starts_with($oldLogoPath, 'logos/')) {
                    Storage::disk('public')->delete($oldLogoPath);
                }
                // If no new file uploaded and no manual URL, set to null
                if (! $this->logoFile && empty($this->value)) {
                    $this->value = null;
                }
            }

            // Upload new logo file if provided
            if ($this->logoFile) {
                // Delete old logo if exists (only if it's a stored file)
                $oldLogoPath = $this->setting->value;
                if ($oldLogoPath && str_starts_with($oldLogoPath, 'logos/')) {
                    Storage::disk('public')->delete($oldLogoPath);
                }

                // Store new logo
                $logoPath = $this->logoFile->store('logos', 'public');
                $this->value = $logoPath;
            }

            // If no file upload and not removing, keep existing value or use manual URL
            if (! $this->logoFile && ! $this->removeExistingLogo) {
                // If value is empty, keep existing value
                if (empty($this->value)) {
                    $this->value = $this->setting->value;
                }
                // Otherwise, use the manually entered URL/value
            }
        }

        // Update setting
        $this->setting->update([
            'value' => $this->value ?? $validated['value'] ?? null,
            'description' => $validated['description'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        // Save translations
        $this->saveTranslations();

        $this->showUpdateModal = false;

        $this->dispatch('setting-updated', [
            'message' => __('common.messages.updated_successfully'),
        ]);

        $this->redirect(route('admin.settings.index'), navigate: true);
    }

    /**
     * Save translations for description and value (if string type).
     */
    protected function saveTranslations(): void
    {
        foreach ($this->translations as $languageCode => $fields) {
            // Save description translation
            if (! empty($fields['description'])) {
                $this->setting->setTranslation('description', $languageCode, $fields['description']);
            } else {
                // Delete translation if empty
                $this->setting->deleteTranslation('description', $languageCode);
            }

            // Save value translation (only for string type)
            if ($this->setting->type === 'string' && isset($fields['value'])) {
                if (! empty($fields['value'])) {
                    $this->setting->setTranslation('value', $languageCode, $fields['value']);
                } else {
                    // Delete translation if empty
                    $this->setting->deleteTranslation('value', $languageCode);
                }
            }
        }
    }

    /**
     * Get translated label for type.
     */
    public function getTypeLabel(string $type): string
    {
        return match ($type) {
            'string' => __('Texto'),
            'integer' => __('Número'),
            'boolean' => __('Booleano'),
            'json' => __('JSON'),
            default => ucfirst($type),
        };
    }

    /**
     * Get translated label for group.
     */
    public function getGroupLabel(string $group): string
    {
        return match ($group) {
            'general' => __('General'),
            'email' => __('Email'),
            'rgpd' => __('RGPD'),
            'media' => __('Media'),
            'seo' => __('SEO'),
            default => ucfirst($group),
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.settings.edit')
            ->layout('components.layouts.app', [
                'title' => __('Editar Configuración'),
            ]);
    }
}
