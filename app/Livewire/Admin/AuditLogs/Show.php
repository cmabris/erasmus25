<?php

namespace App\Livewire\Admin\AuditLogs;

use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\View\View;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

class Show extends Component
{
    use AuthorizesRequests;

    /**
     * The activity log to display.
     */
    public Activity $activity;

    /**
     * Mount the component.
     */
    public function mount(Activity $activity): void
    {
        $this->authorize('view', $activity);

        // Load relationships with eager loading to avoid N+1 queries
        $this->activity = $activity->load(['causer', 'subject']);
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
     * Get changes from properties (old vs attributes).
     *
     * @return array<string, array{field: string, old: mixed, new: mixed}>
     */
    public function getChangesFromProperties(array|\Illuminate\Support\Collection|null $properties): array
    {
        if (! $properties) {
            return [];
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        $changes = [];

        if (isset($properties['old']) && isset($properties['attributes'])) {
            $allKeys = array_unique(array_merge(
                array_keys($properties['old'] ?? []),
                array_keys($properties['attributes'] ?? [])
            ));

            foreach ($allKeys as $key) {
                $oldValue = $properties['old'][$key] ?? null;
                $newValue = $properties['attributes'][$key] ?? null;

                if ($oldValue !== $newValue) {
                    $changes[$key] = [
                        'field' => $key,
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];
                }
            }
        }

        return $changes;
    }

    /**
     * Format value for display.
     */
    public function formatValueForDisplay($value): string
    {
        if ($value === null) {
            return '<span class="text-zinc-500 dark:text-zinc-400 italic">null</span>';
        }

        if (is_bool($value)) {
            return $value ? '<span class="text-green-600 dark:text-green-400">true</span>' : '<span class="text-red-600 dark:text-red-400">false</span>';
        }

        if (is_array($value) || is_object($value)) {
            return '<code class="text-xs">'.htmlspecialchars(json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)).'</code>';
        }

        if (is_string($value) && strlen($value) > 100) {
            return '<span class="break-words">'.htmlspecialchars(substr($value, 0, 100)).'...</span>';
        }

        return htmlspecialchars((string) $value);
    }

    /**
     * Format JSON for display.
     */
    public function formatJsonForDisplay($data): string
    {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = $decoded;
            }
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Get IP address from properties.
     */
    public function getIpAddress(array|\Illuminate\Support\Collection|null $properties): ?string
    {
        if (! $properties) {
            return null;
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        return $properties['ip_address'] ?? $properties['ip'] ?? null;
    }

    /**
     * Get user agent from properties.
     */
    public function getUserAgent(array|\Illuminate\Support\Collection|null $properties): ?string
    {
        if (! $properties) {
            return null;
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        return $properties['user_agent'] ?? $properties['userAgent'] ?? null;
    }

    /**
     * Parse user agent for display.
     */
    public function parseUserAgent(?string $userAgent): ?array
    {
        if (! $userAgent) {
            return null;
        }

        $info = [
            'raw' => $userAgent,
            'browser' => null,
            'os' => null,
            'device' => null,
        ];

        // Simple parsing (could use a library like jenssegers/agent for better results)
        if (preg_match('/(Chrome|Firefox|Safari|Edge|Opera|IE)\/([\d.]+)/i', $userAgent, $matches)) {
            $info['browser'] = $matches[1].' '.$matches[2];
        }

        if (preg_match('/(Windows|Mac|Linux|Android|iOS)/i', $userAgent, $matches)) {
            $info['os'] = $matches[1];
        }

        if (preg_match('/(Mobile|Tablet|iPad|iPhone)/i', $userAgent)) {
            $info['device'] = 'Mobile';
        } else {
            $info['device'] = 'Desktop';
        }

        return $info;
    }

    /**
     * Check if there are changes in properties.
     */
    public function hasChanges(): bool
    {
        return ! empty($this->getChangesFromProperties($this->activity->properties));
    }

    /**
     * Get custom properties (excluding old/attributes).
     */
    public function getCustomProperties(array|\Illuminate\Support\Collection|null $properties): array
    {
        if (! $properties) {
            return [];
        }

        // Convert Collection to array if needed
        if ($properties instanceof \Illuminate\Support\Collection) {
            $properties = $properties->toArray();
        }

        $custom = [];
        $excluded = ['old', 'attributes', 'ip_address', 'ip', 'user_agent', 'userAgent'];

        foreach ($properties as $key => $value) {
            if (! in_array($key, $excluded)) {
                $custom[$key] = $value;
            }
        }

        return $custom;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('livewire.admin.audit-logs.show')
            ->layout('components.layouts.app', [
                'title' => __('Detalle de Log de Auditoría'),
            ]);
    }
}
