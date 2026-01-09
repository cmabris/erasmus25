<?php

namespace App\Livewire\Admin\Settings;

use App\Models\Setting;
use App\Support\Permissions;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    use AuthorizesRequests;

    /**
     * Search query for filtering settings.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by group.
     */
    #[Url(as: 'grupo')]
    public string $filterGroup = '';

    /**
     * Field to sort by.
     */
    #[Url(as: 'ordenar')]
    public string $sortField = 'group';

    /**
     * Sort direction (asc or desc).
     */
    #[Url(as: 'direccion')]
    public string $sortDirection = 'asc';

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Setting::class);
    }

    /**
     * Get filtered and sorted settings grouped by group.
     */
    #[Computed]
    public function settings(): Collection
    {
        return Setting::query()
            ->with(['updater:id,name,email'])
            ->withCount('translations')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('key', 'like', "%{$this->search}%")
                        ->orWhere('value', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterGroup, fn ($query) => $query->where('group', $this->filterGroup))
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('key', 'asc')
            ->get()
            ->groupBy('group');
    }

    /**
     * Get all available groups.
     */
    #[Computed]
    public function availableGroups(): array
    {
        return Setting::query()
            ->distinct()
            ->pluck('group')
            ->sort()
            ->values()
            ->toArray();
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
    }

    /**
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset(['search', 'filterGroup']);
        $this->filterGroup = '';
    }

    /**
     * Handle search input changes.
     */
    public function updatedSearch(): void
    {
        // No pagination, so no need to reset page
    }

    /**
     * Handle filter changes.
     */
    public function updatedFilterGroup(): void
    {
        // No pagination, so no need to reset page
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
     * Format value for display based on type.
     */
    public function formatValue(Setting $setting): string
    {
        $value = $setting->value;

        return match ($setting->type) {
            'boolean' => $value ? __('Sí') : __('No'),
            'json' => is_array($value) || is_object($value)
                ? __('JSON Object').' ('.count((array) $value).' '.__('elementos').')'
                : __('JSON'),
            'integer' => number_format((int) $value, 0, ',', '.'),
            default => is_string($value) && strlen($value) > 100
                ? substr($value, 0, 100).'...'
                : (string) $value,
        };
    }

    /**
     * Check if value is truncated (for tooltip display).
     */
    public function isValueTruncated(Setting $setting): bool
    {
        if ($setting->type !== 'string') {
            return false;
        }

        $value = $setting->value;

        return is_string($value) && strlen($value) > 100;
    }

    /**
     * Get full value for display in tooltip.
     */
    public function getFullValue(Setting $setting): string
    {
        $value = $setting->value;

        if ($setting->type === 'json' && (is_array($value) || is_object($value))) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

        return (string) $value;
    }

    /**
     * Get badge variant for type.
     */
    public function getTypeBadgeVariant(string $type): string
    {
        return match ($type) {
            'string' => 'primary',
            'integer' => 'info',
            'boolean' => 'success',
            'json' => 'warning',
            default => 'ghost',
        };
    }

    /**
     * Get badge variant for group.
     */
    public function getGroupBadgeVariant(string $group): string
    {
        return match ($group) {
            'general' => 'primary',
            'email' => 'info',
            'rgpd' => 'warning',
            'media' => 'success',
            'seo' => 'ghost',
            default => 'ghost',
        };
    }

    /**
     * Check if user can edit settings.
     */
    public function canEdit(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        // Simply check if user has the permission
        return $user->can(Permissions::SETTINGS_EDIT);
    }

    /**
     * Check if setting has translations.
     */
    public function hasTranslations(Setting $setting): bool
    {
        return ($setting->translations_count ?? 0) > 0;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.settings.index')
            ->layout('components.layouts.app', [
                'title' => __('Configuración del Sistema'),
            ]);
    }
}
