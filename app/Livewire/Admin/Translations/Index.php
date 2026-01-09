<?php

namespace App\Livewire\Admin\Translations;

use App\Models\Language;
use App\Models\Program;
use App\Models\Setting;
use App\Models\Translation;
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
     * Search query for filtering translations.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by translatable model type.
     */
    #[Url(as: 'modelo')]
    public ?string $filterModel = null;

    /**
     * Filter by language ID.
     */
    #[Url(as: 'idioma')]
    public ?int $filterLanguageId = null;

    /**
     * Filter by translatable ID.
     */
    #[Url(as: 'registro')]
    public ?int $filterTranslatableId = null;

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'created_at';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'desc';

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
     * Translation ID to delete (for confirmation).
     */
    public ?int $translationToDelete = null;

    /**
     * Cache for translatable models to avoid N+1 queries.
     *
     * @var array<string, mixed>
     */
    protected array $translatableCache = [];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Translation::class);
    }

    /**
     * Get paginated and filtered translations.
     */
    #[Computed]
    public function translations(): LengthAwarePaginator
    {
        return Translation::query()
            ->with(['language'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('field', 'like', "%{$this->search}%")
                        ->orWhere('value', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterModel, function ($query) {
                $query->where('translatable_type', $this->filterModel);
            })
            ->when($this->filterLanguageId, function ($query) {
                $query->where('language_id', $this->filterLanguageId);
            })
            ->when($this->filterTranslatableId, function ($query) {
                $query->where('translatable_id', $this->filterTranslatableId);
            })
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
     * Confirm delete action.
     */
    public function confirmDelete(int $translationId): void
    {
        $this->translationToDelete = $translationId;
        $this->showDeleteModal = true;
    }

    /**
     * Delete a translation.
     */
    public function delete(): void
    {
        if (! $this->translationToDelete) {
            return;
        }

        $translation = Translation::findOrFail($this->translationToDelete);

        $this->authorize('delete', $translation);

        $translation->delete();

        $this->translationToDelete = null;
        $this->showDeleteModal = false;
        $this->resetPage();

        $this->dispatch('translation-deleted', [
            'message' => __('common.messages.deleted_successfully'),
        ]);
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterModel', 'filterLanguageId', 'filterTranslatableId']);
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
    public function updatedFilterModel(): void
    {
        $this->filterTranslatableId = null; // Reset translatable ID when model changes
        $this->resetPage();
    }

    /**
     * Handle language filter changes.
     */
    public function updatedFilterLanguageId(): void
    {
        $this->resetPage();
    }

    /**
     * Handle translatable ID filter changes.
     */
    public function updatedFilterTranslatableId(): void
    {
        $this->resetPage();
    }

    /**
     * Check if user can create translations.
     */
    public function canCreate(): bool
    {
        return auth()->user()?->can('create', Translation::class) ?? false;
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
        if (! $this->filterModel) {
            return collect();
        }

        return match ($this->filterModel) {
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
     * Get display name for translatable model.
     */
    public function getTranslatableDisplayName(Translation $translation): string
    {
        $translatable = $this->getTranslatableModel($translation);

        if (! $translatable) {
            return __('Registro eliminado');
        }

        return match ($translation->translatable_type) {
            Program::class => $translatable->code.' - '.$translatable->name,
            Setting::class => $translatable->key,
            default => class_basename($translation->translatable_type).' #'.$translation->translatable_id,
        };
    }

    /**
     * Get translatable model (including soft-deleted).
     * Uses cache to avoid N+1 queries.
     */
    public function getTranslatableModel(Translation $translation)
    {
        if (! $translation->translatable_type || ! $translation->translatable_id) {
            return null;
        }

        $cacheKey = $translation->translatable_type.'_'.$translation->translatable_id;

        if (isset($this->translatableCache[$cacheKey])) {
            return $this->translatableCache[$cacheKey];
        }

        // Try to get the model, including soft-deleted ones
        $modelClass = $translation->translatable_type;

        if (! class_exists($modelClass)) {
            $this->translatableCache[$cacheKey] = null;

            return null;
        }

        // Check if model uses SoftDeletes
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive($modelClass),
            true
        );

        $model = $usesSoftDeletes
            ? $modelClass::withTrashed()->find($translation->translatable_id)
            : $modelClass::find($translation->translatable_id);

        $this->translatableCache[$cacheKey] = $model;

        return $model;
    }

    /**
     * Check if translatable model is soft-deleted.
     */
    public function isTranslatableDeleted(Translation $translation): bool
    {
        $translatable = $this->getTranslatableModel($translation);

        if (! $translatable) {
            return true;
        }

        // Check if model uses SoftDeletes and is trashed
        $usesSoftDeletes = in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(get_class($translatable)),
            true
        );

        return $usesSoftDeletes && $translatable->trashed();
    }

    /**
     * Get detailed information about translatable model for tooltip.
     */
    public function getTranslatableTooltip(Translation $translation): string
    {
        $translatable = $this->getTranslatableModel($translation);

        if (! $translatable) {
            return __('El registro asociado ha sido eliminado permanentemente.');
        }

        $isDeleted = $this->isTranslatableDeleted($translation);
        $deletedInfo = $isDeleted ? "\n".__('⚠️ Este registro está eliminado (soft delete)') : '';

        return match ($translation->translatable_type) {
            Program::class => sprintf(
                __('Programa: %s (%s)%s'),
                $translatable->name,
                $translatable->code,
                $deletedInfo
            ),
            Setting::class => sprintf(
                __('Configuración: %s%s'),
                $translatable->key,
                $deletedInfo
            ),
            default => sprintf(
                __('%s #%s%s'),
                class_basename($translation->translatable_type),
                $translation->translatable_id,
                $deletedInfo
            ),
        };
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
            Setting::class => __('Configuración'),
            default => class_basename($modelType),
        };
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.translations.index')
            ->layout('components.layouts.app', [
                'title' => __('Traducciones'),
            ]);
    }
}
