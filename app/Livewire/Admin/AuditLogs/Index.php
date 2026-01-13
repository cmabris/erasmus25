<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Exports\AuditLogsExport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    /**
     * Search query for filtering activities.
     */
    #[Url(as: 'q')]
    public string $search = '';

    /**
     * Filter by subject model type.
     */
    #[Url(as: 'modelo')]
    public ?string $filterModel = null;

    /**
     * Filter by causer (user) ID.
     */
    #[Url(as: 'usuario')]
    public ?int $filterCauserId = null;

    /**
     * Filter by description/action.
     */
    #[Url(as: 'accion')]
    public ?string $filterDescription = null;

    /**
     * Filter by log name.
     */
    #[Url(as: 'log')]
    public ?string $filterLogName = null;

    /**
     * Filter by date from.
     */
    #[Url(as: 'desde')]
    public ?string $filterDateFrom = null;

    /**
     * Filter by date to.
     */
    #[Url(as: 'hasta')]
    public ?string $filterDateTo = null;

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
    public int $perPage = 25;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('viewAny', Activity::class);
    }

    /**
     * Get paginated and filtered activities.
     * Optimized with eager loading and indexed queries.
     */
    #[Computed]
    public function activities(): LengthAwarePaginator
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', "%{$this->search}%")
                        ->orWhere('subject_type', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterModel, function ($query) {
                $query->where('subject_type', $this->filterModel);
            })
            ->when($this->filterCauserId, function ($query) {
                $query->where('causer_id', $this->filterCauserId)
                    ->where('causer_type', User::class);
            })
            ->when($this->filterDescription, function ($query) {
                $query->where('description', $this->filterDescription);
            })
            ->when($this->filterLogName, function ($query) {
                $query->where('log_name', $this->filterLogName);
            })
            ->when($this->filterDateFrom, function ($query) {
                $query->whereDate('created_at', '>=', $this->filterDateFrom);
            })
            ->when($this->filterDateTo, function ($query) {
                $query->whereDate('created_at', '<=', $this->filterDateTo);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->orderBy('id', 'desc') // Secondary sort for consistent pagination
            ->paginate($this->perPage);
    }

    /**
     * Get available models for filter (cached).
     */
    #[Computed]
    public function availableModels(): \Illuminate\Support\Collection
    {
        return Cache::remember('audit-logs.available-models', 3600, function () {
            return Activity::query()
                ->distinct()
                ->whereNotNull('subject_type')
                ->pluck('subject_type')
                ->sort()
                ->values();
        });
    }

    /**
     * Get available causers (users) for filter (cached).
     */
    #[Computed]
    public function availableCausers(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::remember('audit-logs.available-causers', 1800, function () {
            $userIds = Activity::query()
                ->where('causer_type', User::class)
                ->whereNotNull('causer_id')
                ->distinct()
                ->pluck('causer_id');

            return User::query()
                ->whereIn('id', $userIds)
                ->orderBy('name')
                ->get(['id', 'name', 'email']);
        });
    }

    /**
     * Get available descriptions for filter.
     */
    #[Computed]
    public function availableDescriptions(): \Illuminate\Support\Collection
    {
        return Activity::query()
            ->distinct()
            ->whereNotNull('description')
            ->pluck('description')
            ->sort()
            ->values();
    }

    /**
     * Get available log names for filter.
     */
    #[Computed]
    public function availableLogNames(): \Illuminate\Support\Collection
    {
        return Activity::query()
            ->distinct()
            ->whereNotNull('log_name')
            ->pluck('log_name')
            ->sort()
            ->values();
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
     * Reset filters to default values.
     */
    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'filterModel',
            'filterCauserId',
            'filterDescription',
            'filterLogName',
            'filterDateFrom',
            'filterDateTo',
        ]);
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
        $this->resetPage();
    }

    /**
     * Handle causer filter changes.
     */
    public function updatedFilterCauserId(): void
    {
        $this->resetPage();
    }

    /**
     * Handle description filter changes.
     */
    public function updatedFilterDescription(): void
    {
        $this->resetPage();
    }

    /**
     * Handle log name filter changes.
     */
    public function updatedFilterLogName(): void
    {
        $this->resetPage();
    }

    /**
     * Handle date from filter changes.
     */
    public function updatedFilterDateFrom(): void
    {
        $this->resetPage();
    }

    /**
     * Handle date to filter changes.
     */
    public function updatedFilterDateTo(): void
    {
        $this->resetPage();
    }

    /**
     * Get model display name.
     */
    public function getModelDisplayName(?string $subjectType): string
    {
        if (! $subjectType) {
            return '-';
        }

        return match ($subjectType) {
            'App\Models\Program' => __('Programa'),
            'App\Models\Call' => __('Convocatoria'),
            'App\Models\NewsPost' => __('Noticia'),
            'App\Models\Document' => __('Documento'),
            'App\Models\ErasmusEvent' => __('Evento'),
            'App\Models\AcademicYear' => __('Año Académico'),
            'App\Models\DocumentCategory' => __('Categoría de Documento'),
            'App\Models\NewsTag' => __('Etiqueta de Noticia'),
            'App\Models\CallPhase' => __('Fase de Convocatoria'),
            'App\Models\Resolution' => __('Resolución'),
            default => class_basename($subjectType),
        };
    }

    /**
     * Get description display name.
     */
    public function getDescriptionDisplayName(string $description): string
    {
        return match (strtolower($description)) {
            'created' => __('Creado'),
            'updated' => __('Actualizado'),
            'deleted' => __('Eliminado'),
            'publish' => __('Publicado'),
            'published' => __('Publicado'),
            'archive' => __('Archivado'),
            'archived' => __('Archivado'),
            'restore' => __('Restaurado'),
            'restored' => __('Restaurado'),
            default => ucfirst($description),
        };
    }

    /**
     * Get description badge variant.
     */
    public function getDescriptionBadgeVariant(string $description): string
    {
        return match (strtolower($description)) {
            'created', 'publish', 'published', 'restore', 'restored' => 'success',
            'updated' => 'info',
            'deleted', 'archive', 'archived' => 'danger',
            default => 'neutral',
        };
    }

    /**
     * Get subject URL if available.
     */
    public function getSubjectUrl(?string $subjectType, ?int $subjectId): ?string
    {
        if (! $subjectType || ! $subjectId) {
            return null;
        }

        $routeMap = [
            'App\Models\Program' => 'admin.programs.show',
            'App\Models\Call' => 'admin.calls.show',
            'App\Models\NewsPost' => 'admin.news.show',
            'App\Models\Document' => 'admin.documents.show',
            'App\Models\ErasmusEvent' => 'admin.events.show',
            'App\Models\AcademicYear' => 'admin.academic-years.show',
            'App\Models\DocumentCategory' => 'admin.document-categories.show',
            'App\Models\NewsTag' => 'admin.news-tags.show',
        ];

        $routeName = $routeMap[$subjectType] ?? null;

        if (! $routeName) {
            return null;
        }

        try {
            return route($routeName, $subjectId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get subject title for display.
     */
    public function getSubjectTitle($subject): string
    {
        if (! $subject) {
            return '-';
        }

        // Try common title/name fields
        if (isset($subject->title)) {
            return $subject->title;
        }

        if (isset($subject->name)) {
            return $subject->name;
        }

        // Fallback to ID
        return __('Registro #:id', ['id' => $subject->id ?? '-']);
    }

    /**
     * Format changes summary from properties.
     */
    public function formatChangesSummary(array|\Illuminate\Support\Collection|null $properties): string
    {
        if (! $properties) {
            return '-';
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        $changes = [];

        // Extract changes from properties (old vs attributes)
        if (isset($properties['old']) && isset($properties['attributes'])) {
            foreach ($properties['old'] as $key => $oldValue) {
                $newValue = $properties['attributes'][$key] ?? null;
                if ($oldValue !== $newValue) {
                    $changes[] = $key;
                }
            }
        }

        if (empty($changes)) {
            return __('Sin cambios');
        }

        $count = count($changes);
        $displayed = array_slice($changes, 0, 3);

        $summary = implode(', ', $displayed);
        if ($count > 3) {
            $summary .= ' '.__('y :count más', ['count' => $count - 3]);
        }

        return $summary;
    }

    /**
     * Export activities to Excel.
     */
    public function export(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('viewAny', Activity::class);

        $filters = [
            'search' => $this->search,
            'filterModel' => $this->filterModel,
            'filterCauserId' => $this->filterCauserId,
            'filterDescription' => $this->filterDescription,
            'filterLogName' => $this->filterLogName,
            'filterDateFrom' => $this->filterDateFrom,
            'filterDateTo' => $this->filterDateTo,
            'sortField' => $this->sortField,
            'sortDirection' => $this->sortDirection,
        ];

        $fileName = 'audit-logs-'.now()->format('Y-m-d-His').'.xlsx';

        return Excel::download(new AuditLogsExport($filters), $fileName);
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.audit-logs.index')
            ->layout('components.layouts.app', [
                'title' => __('Auditoría y Logs'),
            ]);
    }
}
